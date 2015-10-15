require 'spec_helper'

describe "Test d'une commande < 100 € sur " + ENV['TEST_SERVER'] do

	it "ajoute un produit < 100 € au panier" do
    session.visit '/?s=50'
    first(:xpath, '//a[text()="Test 50"]').click
    first(:xpath, '//button[@class="single_add_to_cart_button button alt"]').click
    find(:xpath, '//a[text()="View Cart"]').click
    find(:xpath, '//a[@class="checkout-button button alt wc-forward"]').click
    find(:xpath, '//a[text()="Click here to login"]').click
    fill_in 'username', :with => ENV['CUSTOMER_FIRSTNAME']
    fill_in 'password', :with => ENV['CUSTOMER_PASSWD']
    find(:xpath, '//input[@value="Login"]').click
    $stdin.gets
	end

	it "passe commande" do
		session.click_link_or_button 'Proceed to checkout'
	end

	it "s'identifie" do
		find('#email').set ENV['CUSTOMER_EMAIL']
		find('#passwd').set ENV['CUSTOMER_PASSWD']
		find_button('SubmitLogin').click
		expect(page).to have_content ENV['CUSTOMER_NAME']
	end

	it "retourne au panier" do
		visit '/index.php?controller=order'
		find('a', text: 'Proceed to checkout').click
	end

  it "passe les adresses" do
		find("button[name=processAddress]").click
    expect(page).to have_content('terms of service')
	end

	it "valide les CGV et confirme" do
		expect(page).to have_selector('#uniform-cgv')
		find('label[for=cgv]').click
		find(:xpath, '//button[@class="button btn btn-default standard-checkout button-medium"]').click
		expect(page).to have_content ENV['MODULE_PAY_ACTION_TEXT']
		expect(page).to have_content 'Continue shopping'
		expect(page).to have_content 'CHOOSE YOUR PAYMENT METHOD'
	end

	it "choisit l'option " + ENV['MODULE_NAME'] do
		find('a.' + ENV['MODULE_NAME'].downcase).click
	end

	it "confirme la commande avec CashWay, constate une alerte KYC" do
		fail "Le service ne fonctionne pas..." if page.has_content? 'Hélas'
		expect(page).to have_content 'Total à payer au buraliste'
		expect(page).to have_content 'Please confirm your order by clicking'
		expect(page).to have_content 'J\'ai lu les conditions d’utilisation de CashWay et j’y adhère sans réserve'
		expect(page).to have_content 'Les distributeurs proches de chez vous'
		#session.click_button 'Je confirme ma commande'
		find('label[for=cgu-accept]').click
		find('#cashway-confirm-btn').click
	end

	it "consulte la marche à suivre, constate liens vers formulaire KYC" do
		#expect(page).to have_content 'Confirmation de commande'
		expect(page).to have_content 'rendez-vous dans un des points de paiement indiqués sur notre carte, muni du code suivant'
		expect(page).to have_content 'Please note and keep your order reference'
		barcode = find('#cashway-barcode-label').text
		#expect(barcode[0..6]).to eq '3663538'

		#mail = URI.parse(page.find('a[id = "cashway-kyc-email"]')['href'])
		#expect(mail.to).to eq 'validation@cashway.fr'

		#form = URI.parse(page.find('a[id = "cashway-kyc-form"]')['href'])
		#expect(form.scheme).to eq 'https'
		#expect(form.host.end_with?('.cashway.fr')).to be true

		# TODO, ajouter les expect() pour les liens vers mail & formulaire KYC
		# TODO télécharger/imprimer ticket de paiement
		# TODO liste texte des bureaux de paiement
	end
end
