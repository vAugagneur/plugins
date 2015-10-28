require 'spec_helper'

MODULE_ANCHOR='anchor' + ENV['MODULE_NAME'].downcase.capitalize

describe "Delete + install of CashWay module on PrestaShop: " + ENV['TEST_SERVER'] do

	it "loads admin page" do
		session.visit ENV['ADMIN_PATH']
	end

	it "authenticates" do
		find('#user_login').set ENV['ADMIN_LASTNAME']
		find('#user_pass').set ENV['ADMIN_PASSWD']
		find('#wp-submit').click
	end

  it 'install WooCommerce' do
    skip('Already installed') if page.first('#toplevel_page_woocommerce')

    session.visit '/wp-admin/plugin-install.php?tab=search&type=term&s=woocommerce'
    find(:xpath, '//a[@data-slug="woocommerce"]').click
    first(:xpath, '//a[@target="_parent"]').click
    find(:xpath, '//a[text()="Let\'s Go!"]').click
    find(:xpath, '//input[@value="Continue"]').click
    find(:xpath, '//input[@value="Continue"]').click
    find(:xpath, '//input[@value="Continue"]').click
    find(:xpath, '//input[@value="Continue"]').click
  end
end
