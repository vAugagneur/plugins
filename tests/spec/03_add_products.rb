require 'spec_helper'

describe "Ajout de produits test 250€/2500€" do
	it "Charge la page admin" do
		session.visit ENV['ADMIN_PATH']
	end

	it "S'identifie" do
		find('#passwd').set ENV['ADMIN_PASSWD']
		find('#email').set ENV['ADMIN_EMAIL']
		find('button[name=submitLogin]').click
	end

	it "Ajoute un produit à 250€" do
		find('li#maintab-AdminCatalog').find('a.title').click
		click_link_or_button('desc-product-new')
		fill_in 'name_1', :with => 'Test 250'
		click_link_or_button('link-Prices')
		fill_in 'priceTI', :with => '250'
		sleep(3.5)
		find('button[name=submitAddproductAndStay]').click
		click_link_or_button('link-Quantities')
		fill_in 'qty_0', :with => '10000'
	end

	it "Ajoute un produit à 2500€" do
		find('li#maintab-AdminCatalog').find('a.title').click
		click_link_or_button('desc-product-new')
		fill_in 'name_1', :with => 'Test 2500'
		click_link_or_button('link-Prices')
		fill_in 'priceTI', :with => '2500'
		sleep(3.5)
		find('button[name=submitAddproductAndStay]').click
		click_link_or_button('link-Quantities')
		fill_in 'qty_0', :with => '10000'
		find('li#maintab-AdminCatalog').find('a.title').click		
	end
end
