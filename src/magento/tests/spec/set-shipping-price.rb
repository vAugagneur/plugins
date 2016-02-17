require 'spec_helper'

describe "Set shipping price" do
	it "Charge la page admin" do
		session.visit ENV['ADMIN_PATH']
	end

	it "S'identifie" do
		find('#login').set ENV['ADMIN_PASSWD']
		find('#username').set ENV['ADMIN_FIRSTNAME']
		first('.form-button').click
	end

	it "set shipping price" do
		if page.first('#message-popup-window')
			find(:xpath, '//a[@title = "close"]').click
		end
		find(:xpath, '//span[text()="System"]').click
    find(:xpath, '//span[text()="Configuration"]').click
    find(:xpath, '//span[normalize-space(text())="Shipping Methods"]').click
    find(:xpath, '//a[@id="carriers_flatrate-head"]').click
    fill_in 'carriers_flatrate_price', :with => '0'
    find(:xpath, '//button[@class="scalable save"]').click
    sleep(3);
  	end
end
