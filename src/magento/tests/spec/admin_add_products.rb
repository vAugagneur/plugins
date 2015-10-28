require 'spec_helper'


rngnb = rand(2000).to_s
describe "Ajout de produits test 1€" do
	it "Charge la page admin" do
		session.visit ENV['ADMIN_PATH']
	end

	it "S'identifie" do
		find('#login').set ENV['ADMIN_PASSWD']
		find('#username').set ENV['ADMIN_FIRSTNAME']
		first('.form-button').click
	end

	it "Va dans le catalogue" do
		if page.first('#message-popup-window')
			find(:xpath, '//a[@title = "close"]').click
		end
		find(:xpath, '//ul[@id="nav"]/li/a/span[text()="Catalog"]').click
	end

	it "Va dans Manage Products" do
		find(:xpath, '//ul[@id="nav"]/li[3]/ul/li/a/span[text()="Manage Products"]').click
		find(:xpath, '//button[@title="Add Product"]').click
		find(:xpath, '//span[@id="continue_button"]/button[@title="Continue"]').click
	end

	it "Ajoute un produit à 1€" do
		fill_in 'name', :with => 'test'
		fill_in 'description', :with => 'test'
		fill_in 'short_description', :with => 'test'
		fill_in 'sku', :with => rngnb
		fill_in 'weight', :with => '250'
		find(:xpath, '//select[@id="status"]/option[@value="1"]').click
		find(:xpath, '//select[@id="visibility"]/option[@value="4"]').click
		find(:xpath, '//button[@title="Save"]').click
		fill_in 'price', :with => '1'
		find(:xpath, '//select[@id="tax_class_id"]/option[@value="0"]').click
		find(:xpath, '//a[@id="product_info_tabs_inventory"]').click
		fill_in 'inventory_qty', :with => '100000'
		find(:xpath, '//select[@id="inventory_stock_availability"]/option[@value="1"]').click
		find(:xpath, '//button[@title="Save"]').click
	end
end
