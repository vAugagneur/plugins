require 'spec_helper'

describe "Test d'une commande > 2500 € sur " + ENV['TEST_SERVER'] do

	it "se rend sur le site" do
		session.visit '/'
	end

	it "s'identifie" do
		find(:xpath, '//a[@class="skip-link skip-account"]/span[@class="label"]').click
		first(:xpath, '//a[@title="My Account"]').click
		fill_in 'login[username]', :with => 'testpacaud@gmail.com'
		fill_in 'pass', :with => 'cashway'
		find(:xpath, '//div[@class="buttons-set"]/button[@type="submit"]').click
	end

	it "vide le panier" do
		find(:xpath, '//a[@class="skip-link skip-account"]/span[@class="label"]').click
		first(:xpath, '//a[@class="top-link-cart"]').click
		if page.first('#empty_cart_button')
			find(:xpath, '//button[@id="empty_cart_button"]').click
		end
	end

	it "ajoute 2500€ au panier" do
		fill_in 'search', :with => 'test'
		find(:xpath, '//div[@class="input-box"]/button[@class="button search-button"]').click
		first(:xpath, '//h2[@class="product-name"]/a[@title="test"]/../../div[@class="actions"]/button[@class="button btn-cart"]').click
		find(:xpath, '//a[@title="Edit item parameters"]').click
		fill_in 'qty', :with => '2500'
		find(:xpath, '//button[@class="button btn-cart"]').click
		first(:xpath, '//button[@class="button btn-proceed-checkout btn-checkout"]').click
	end

	it "choisit sa méthode de paiement" do
		find(:xpath, '//button[@title="Continue"]').click
		find(:xpath, '//button[@onclick="shippingMethod.save()"]').click
		find(:xpath, '//input[@title="Cashway payment"]').click
		find(:xpath, '//button[@onclick="payment.save()"]').click
		find(:xpath, '//button[@class="button btn-checkout"]').click
	end
end
