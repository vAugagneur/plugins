require 'spec_helper'

describe "Test d'une commande > 2500 € sur " + ENV['TEST_SERVER'] do

	it "ajoute un produit > 2500 € au panier" do
    session.visit '/?s=50'
    first(:xpath, '//a[text()="Test 2500"]').click
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
		session.click_button 'Proceed to checkout'
		expect(page).to have_content 'Payment'
		expect(page).to have_content ENV['MODULE_PAY_ACTION_TEXT']
		expect(page).to have_content 'Continue shopping'
		expect(page).to have_content 'CHOOSE YOUR PAYMENT METHOD'
	end

	it "choisit l'option " + ENV['MODULE_NAME'] do
		find('a.' + ENV['MODULE_NAME'].downcase).click
	end

	it "vérifie l'impossibilité de continuer" do
		expect(page).to have_content 'Hélas, vous avez dépassé le montant maximum possible d\'achats via CashWay sur la période des 12 derniers mois (plus d’informations).'
	end
end
