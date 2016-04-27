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
		fill_in 'billing_first_name', :with => ENV['CUSTOMER_FIRSTNAME']
		fill_in 'billing_last_name', :with => ENV['CUSTOMER_LASTNAME']
		fill_in 'billing_email', :with => ENV['CUSTOMER_EMAIL']
		fill_in 'billing_phone', :with => ENV['CUSTOMER_PHONE']
		fill_in 'billing_address_1', :with => ENV['CUSTOMER_ADDRESS']
		fill_in 'billing_phone', :with => ENV['CUSTOMER_PHONE']
		fill_in 'billing_postcode', :with => ENV['CUSTOMER_ZIPCODE']
		fill_in 'billing_city', :with => ENV['CUSTOMER_CITY']
	end

	it "passe commande" do
		find(:xpath, '//input[@value="Place order"]').click
		expect(page).to have_content 'Votre commande dépasse la limite de montant pour le paiement avec Cashway'
	end

end
