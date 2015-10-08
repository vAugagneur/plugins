require 'spec_helper'

describe "Test d'une première commande sur " + ENV['TEST_SERVER'] do

	it "ajoute un produit au panier" do
		session.visit '/'
		first('.product-container').click_link_or_button 'Ajouter au panier'
		session.click_link_or_button 'Proceed to checkout'
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

		session.click_button 'Je confirme ma commande'
	end

	it "consulte la marche à suivre" do
		#expect(page).to have_content 'Confirmation de commande'
		expect(page).to have_content 'vous pouvez maintenant vous rendre dans un des points de paiement indiqués sur la carte ci-dessous'
		expect(page).to have_content 'Veuillez bien noter et conserver la référence de la commande'

		barcode = find('#cashway-barcode-label').text
		expect(barcode[0..6]).to eq '3663538'

		# TODO télécharger/imprimer ticket de paiement
		# TODO liste texte des bureaux de paiement
	end
end
