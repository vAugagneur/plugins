require 'spec_helper'

describe "Add a new customer user" do

  it "loads shop front page" do
    session.visit '/'
  end

  it "checks for email account" do
    find(:xpath, '//a[@title="Log in to your customer account"]').click
    fill_in 'email_create', with: ENV['CUSTOMER_EMAIL']

    find(:xpath, '//button[@name="SubmitCreate"]').click

    if has_selector?('#create_account_error', visible: true)
      puts "Logs in existing account"
      find('#login_form').fill_in('email', with: ENV['CUSTOMER_EMAIL'])
      find('#login_form').fill_in('passwd', with: ENV['CUSTOMER_PASSWD'])

      find(:xpath, '//button[@id="SubmitLogin"]').click
    else
      puts "Registers account"
      fill_in 'customer_firstname', with: ENV['CUSTOMER_FIRSTNAME']
      fill_in 'customer_lastname', with: ENV['CUSTOMER_LASTNAME']
      fill_in 'passwd', with: ENV['CUSTOMER_PASSWD']

      find(:xpath, '//button[@name="submitAccount"]').click
    end
  end

  it "registers an address" do
    find(:xpath, '//a[@title="Add my first address"]').click
    fill_in 'address1', with: ENV['CUSTOMER_ADRESS']
    fill_in 'city', with: ENV['CUSTOMER_CITY']
    fill_in 'postcode', with: ENV['CUSTOMER_ZIPCODE']
    fill_in 'address1', with: ENV['CUSTOMER_ADRESS']
    fill_in 'phone_mobile', with: ENV['CUSTOMER_PHONE']

    find(:xpath, '//select[@id="id_country"]/option[@value="8"]').click
    find(:xpath, '//button[@id="submitAddress"]').click
  end
end
