require 'spec_helper'

describe "Magento configuration"do

	it "set config" do
    session.visit '/'
		find(:xpath, '//input[@id="agree"]').click
    find(:xpath, '//button[@id="submitButton"]').click
    find(:xpath, '//select[@id="currency"]/option[@value="EUR"]').click
    find(:xpath, '//select[@id="timezone"]/option[@value="Europe/London"]').click
    find(:xpath, '//button[@type="submit"]').click
    find(:xpath, '//input[@id="skip_base_url_validation"]').click
    fill_in 'user', :with => 'magento'
    fill_in 'password', :with => 'magento'
    find(:xpath, '//input[@id="base_url"]')
    session.execute_script("$$('#base_url')[0].removeClassName('validate-url');")
    find(:xpath, '//button[@type="submit"]').click
    fill_in('firstname', :with => ENV['ADMIN_FIRSTNAME'])
    fill_in('lastname', :with => ENV['ADMIN_LASTNAME'])
    fill_in('username', :with => ENV['ADMIN_FIRSTNAME'])
    fill_in('email_address', :with => ENV['ADMIN_EMAIL'])
    fill_in('confirmation', :with => ENV['ADMIN_PASSWD'])
    fill_in('password', :with => ENV['ADMIN_PASSWD'])
    find(:xpath, '//button[@type="submit"]').click
	end
end
