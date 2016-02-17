require 'spec_helper'

describe "Créer un compte client de test" + ENV['TEST_SERVER'] do

	it "va sur le site" do
		session.visit '/'
	end

	it "créer un compte" do
		find(:xpath, '//a[@class="skip-link skip-account"]/span[@class="label"]').click
		find(:xpath, '//a[@title="Register"]').click
		fill_in 'firstname', :with => 'Jean-Baptiste'
		fill_in 'lastname', :with => 'Pacaud'
		fill_in 'email_address', :with => 'testpacaud@gmail.com'
		fill_in 'password', :with => 'cashway'
		fill_in 'confirmation', :with => 'cashway'
		find(:xpath, '//button[@title="Register"]').click
	end

	it "parametre le compte" do
		find(:xpath, '//a[@class="skip-link skip-account"]/span[@class="label"]').click
		first(:xpath, '//a[@title="My Account"]').click
		find(:xpath, '//a[text()="Address Book"]').click
		fill_in 'street_1', :with => '30 rue des pommiers'
		fill_in 'city', :with => 'Nantes'
		fill_in 'zip', :with => '44000'
		fill_in 'telephone', :with => '0659774440'
		find(:xpath, '//select[@id="country"]/option[@value="FR"]').click
		find(:xpath, '//select[@id="region_id"]/option[@value="226"]').click
		find(:xpath, '//button[@title="Save Address"]').click
	end
end
