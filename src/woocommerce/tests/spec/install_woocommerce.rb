require 'spec_helper'

MODULE_ANCHOR='anchor' + ENV['MODULE_NAME'].downcase.capitalize

describe "Delete + install of CashWay module on PrestaShop: " + ENV['TEST_SERVER'] do

  it "loads admin page" do
    session.visit ENV['LOGIN_PATH']
  end

  it "authenticate and display plugins page" do
    find('#user_login').set ENV['ADMIN_LASTNAME']
    find('#user_pass').set ENV['ADMIN_PASSWD']
    find('#wp-submit').click
    sleep 1
    find('#menu-plugins').click
    find(:xpath, "//a[@href='plugin-install.php']").click
    sleep 1
  end

  it "install WooCommerce" do
    skip('Already installed') if page.first('#toplevel_page_woocommerce')

    find(:xpath, '//input[@name="s"]').set 'woocommerce'
    find('#search-submit').click
    find(:xpath, '//a[@data-slug="woocommerce"]').click
    first(:xpath, '//a[@target="_parent"]').click
    sleep 5
    #find(:xpath, '//a[text()="Let\'s Go!"]').click
    #find(:xpath, '//input[@value="Continue"]').click
    #find(:xpath, '//input[@value="Continue"]').click
    #find(:xpath, '//input[@value="Continue"]').click
    #find(:xpath, '//input[@value="Continue"]').click
  end

end
