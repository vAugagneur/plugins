require 'spec_helper'

describe "Magento basic setup"do
  it "loads installation page & agrees license" do
    session.visit '/'
    find(:xpath, '//input[@id="agree"]').click
    find(:xpath, '//button[@id="submitButton"]').click
  end

  it "sets default locale values" do
    if Capybara.current_driver === :poltergeist
      find(:xpath, '//select[@id="currency"]/option[@value="EUR"]').trigger('click')
      find(:xpath, '//select[@id="timezone"]/option[@value="Europe/London"]').trigger('click')
    else
      find(:xpath, '//select[@id="currency"]/option[@value="EUR"]').click
      find(:xpath, '//select[@id="timezone"]/option[@value="Europe/London"]').click
    end
    find(:xpath, '//button[@type="submit"]').click
  end

  it "defines server basic config" do
    find(:xpath, '//input[@id="skip_base_url_validation"]').click
    fill_in 'user', with: 'magento'
    fill_in 'password', with: 'magento'
    find(:xpath, '//input[@id="base_url"]')
    session.execute_script("$$('#base_url')[0].removeClassName('validate-url');")
    find(:xpath, '//button[@type="submit"]').click
  end

  it "sets admin user account" do
    fill_in('firstname', with: ENV['ADMIN_FIRSTNAME'])
    fill_in('lastname', with: ENV['ADMIN_LASTNAME'])
    fill_in('username', with: ENV['ADMIN_USERNAME'])
    fill_in('email_address', with: ENV['ADMIN_EMAIL'])
    fill_in('password', with: ENV['ADMIN_PASSWD'])
    fill_in('confirmation', with: ENV['ADMIN_PASSWD'])
    find(:xpath, '//button[@type="submit"]').click
  end
end
