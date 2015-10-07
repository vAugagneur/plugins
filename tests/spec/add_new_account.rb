require 'spec_helper'

describe "Add a new customer user" do

  it "load shop front page" do
    session.visit '/'
  end

  it "register a new account" do
    find(:xpath, '//a[@title="Log in to your customer account"]').click

    fill_in 'email_create', :with => ENV['CUSTOMER_EMAIL']
    find(:xpath, '//button[@name="SubmitCreate"]').click

    fill_in 'customer_firstname', :with => ENV['CUSTOMER_FIRSTNAME']
    fill_in 'customer_lastname', :with => ENV['CUSTOMER_LASTNAME']
    fill_in 'passwd', :with => ENV['CUSTOMER_PASSWD']
    find(:xpath, '//button[@name="submitAccount"]').click
  end

  it "add an adresse" do
    find(:xpath, '//a[@title="Add my first address"]').click
    fill_in 'address1', :with => ENV['CUSTOMER_ADRESS']
    fill_in 'city', :with => ENV['CUSTOMER_CITY']
    fill_in 'postcode', :with => ENV['CUSTOMER_ZIPCODE']
    fill_in 'address1', :with => ENV['CUSTOMER_ADRESS']
    fill_in 'phone_mobile', :with => ENV['CUSTOMER_PHONE']
    find(:xpath, '//select[@id="id_country"]/option[@value="8"]').click
    find(:xpath, '//button[@id="submitAddress"]').click
  end
end
