require 'spec_helper'

describe "Adds new customer account" do
  it "loads admin page" do
		session.visit ENV['LOGIN_PATH']
	end

  it "authenticates" do
    find('#user_login').set ENV['ADMIN_LASTNAME']
    find('#user_pass').set ENV['ADMIN_PASSWD']
    find('#wp-submit').click
  end

  it "create new user" do
    find('#menu-users').click
    find(:xpath, "//a[@href='user-new.php']").click
    fill_in 'user_login', :with => ENV['CUSTOMER_FIRSTNAME']
    fill_in 'email', :with => 'anne.de.bretagne@do.cshw.pl'
    find(:xpath, '//button[@class="button button-secondary wp-generate-pw hide-if-no-js"]').click
    fill_in 'pass1-text', :with => ENV['CUSTOMER_PASSWD']
    fill_in 'pass1-text', :with => ENV['CUSTOMER_PASSWD']
    find(:xpath, '//input[@name="pw_weak"]').click
    find(:xpath, '//input[@id="createusersub"]').click
    find(:xpath, '//a[text()="Anne"]').click
    fill_in 'billing_address_1', :with => ENV['CUSTOMER_ADRESS']
    fill_in 'billing_city', :with => ENV['CUSTOMER_CITY']
    fill_in 'billing_postcode', :with => ENV['CUSTOMER_ZIPCODE']
    find(:xpath, '//span[@id="select2-chosen-1"]').click
    fill_in 's2id_autogen1_search',  :with => 'France'
    first(:xpath, '//ul[@id="select2-results-1"]/li').click
    find(:xpath, '//input[@id="submit"]').click
  end
end
