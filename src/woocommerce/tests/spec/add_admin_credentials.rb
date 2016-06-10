require 'spec_helper'

describe "Checks if the credentials management in the module's administration works fine" do

  it "loads admin page" do
    session.visit ENV['ADMIN_PATH']
  end

  it "authenticates" do
    find('#user_login').set ENV['ADMIN_EMAIL']
    find('#user_pass').set ENV['ADMIN_PASSWD']
    find('#wp-submit').click
  end

  it "goes to the module's admin page" do
    find('#toplevel_page_woocommerce').click
    find(:xpath, "//a[@href='admin.php?page=wc-settings']").click
    find(:xpath, "//a[contains(@href, 'admin.php?page=wc-settings&tab=checkout')]").click
    first(:xpath, "//a[contains(@href, 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_cashway')]").click
  end

  it "fills in wrong credentials" do
    fill_in 'woocommerce_woocashway_cashway_login', :with => 'test'
    fill_in 'woocommerce_woocashway_cashway_password', :with => 'test'
    find(:xpath, '//input[@value="Save changes"]').click
    begin
      page.driver.browser.switch_to.alert.accept
    rescue Selenium::WebDriver::Error::NoAlertOpenError
      puts "The credentials are accepted while they are supposed to be refused."
    else
      puts "The credentials are refused as they are supposed to."
    end
  end

  it "fills in right credentials" do
    fill_in 'woocommerce_woocashway_cashway_login', :with => ENV['API_KEY']
    fill_in 'woocommerce_woocashway_cashway_password', :with => ENV['API_SECRET']
    find(:xpath, '//input[@value="Save changes"]').click
    begin
      page.driver.browser.switch_to.alert.accept
    rescue Selenium::WebDriver::Error::NoAlertOpenError
      puts "The credentials are accepted as they are supposed to."
    else
      puts "The credentials are refused while they are supposed to be accepted."
    end
  end

end
