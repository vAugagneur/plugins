require 'spec_helper'

describe "Créer un compte client de test" + ENV['TEST_SERVER'] do

  it "va sur le site" do
    session.visit '/'
  end

  it "créer un compte" do
    find(:xpath, '//a[@class="skip-link skip-account"]/span[@class="label"]').click
    find(:xpath, '//a[@title="Register"]').click
    fill_in 'firstname', with: ENV['CUSTOMER_FIRSTNAME']
    fill_in 'lastname', with: ENV['CUSTOMER_LASTNAME']
    fill_in 'email_address', with: ENV['CUSTOMER_EMAIL']
    fill_in 'password', with: ENV['CUSTOMER_PASSWD']
    fill_in 'confirmation', with: ENV['CUSTOMER_PASSWD']
    find(:xpath, '//button[@title="Register"]').click
  end

  it "parametre le compte" do
    find(:xpath, '//a[@class="skip-link skip-account"]/span[@class="label"]').click
    first(:xpath, '//a[@title="My Account"]').click
    find(:xpath, '//a[text()="Address Book"]').click
    fill_in 'street_1', with: ENV['CUSTOMER_ADDRESS']
    fill_in 'city', with: ENV['CUSTOMER_CITY']
    fill_in 'zip', with: ENV['CUSTOMER_ZIP']
    fill_in 'telephone', with: ENV['CUSTOMER_PHONE']
    find(:xpath, '//select[@id="country"]/option[@value="FR"]').click
    find(:xpath, '//select[@id="region_id"]/option[@value="226"]').click
    find(:xpath, '//button[@title="Save Address"]').click
  end
end
