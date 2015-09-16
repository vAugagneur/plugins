require 'spec_helper'

describe "Test d'une commande > 2500 € sur " + ENV['TEST_SERVER'] do

	it "ajoute un produit > 2500 € au panier" do
		session.visit '/'
		first('.product-container').click
		click_link_or_button('Ajouter au panier')
		sleep(1.5)
		visit '/index.php?controller=order'
	end

	it "passe commande" do
		session.click_link_or_button 'Commander'
	end

	it "s'identifie" do
		assert_text 'Connexion'
		expect(page).to have_selector('form#login_form')
		expect(page).to have_selector('#email')
		expect(page).to have_selector('#passwd')

		find('#email').set ENV['CUSTOMER_EMAIL']
		find('#passwd').set ENV['CUSTOMER_PASSWD']
		find_button('SubmitLogin').click
		expect(page).to have_content ENV['CUSTOMER_NAME']
	end

	it "retourne au panier" do
		visit '/index.php?controller=order'
		find('a', text: 'Commander').click
	end

  it "passe les adresses" do
		find("button[name=processAddress]").click
		expect(page).to have_content('conditions générales')
	end

	it "valide les CGV et confirme" do
		expect(page).to have_selector('#uniform-cgv')
		find('label[for=cgv]').click
		session.click_button 'Commander'
		expect(page).to have_content 'Paiement'
		expect(page).to have_content ENV['MODULE_PAY_ACTION_TEXT']
		expect(page).to have_content 'Continuer mes achats'
		expect(page).to have_content 'CHOISISSEZ VOTRE MÉTHODE DE PAIEMENT'
	end

	it "choisit l'option " + ENV['MODULE_NAME'] do
		find('a.' + ENV['MODULE_NAME'].downcase).click
	end

	it "confirme la commande avec CashWay" do
		fail "Le service ne fonctionne pas..." if page.has_content? 'Hélas'
		expect(page).to have_content 'Total à payer au buraliste'
		expect(page).to have_content 'Merci de confirmer votre commande en cliquant'
		expect(page).to have_content 'J\'ai lu les conditions d’utilisation de CashWay et j’y adhère sans réserve'
		expect(page).to have_content 'Les distributeurs proches de chez vous'

		# KYC nécessaires dans ce cas
		expect(page).to have_content 'pour encaisser ce montant, la réglementation française nous impose de contrôler votre identité.'
		#session.click_button 'Je confirme ma commande'
		find('label[for=cgu-accept]').click
		find('#cashway-confirm-btn').click
	end
end
