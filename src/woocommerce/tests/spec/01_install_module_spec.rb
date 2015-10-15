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

	it 'goes to modules list' do
    session.visit 'http://localhost:8080/wordpress/wp-admin/plugin-install.php?tab=upload'
		page.all('input[id="pluginzip"]').first.set File.absolute_path(ENV['MODULE_ARCHIVE'])
    click_button 'install-plugin-submit'
    first(:xpath, '//a[@target="_parent"]').click
    session.visit 'http://localhost:8080/wordpress/wp-admin/admin.php?page=wc-settings&tab=checkout&section=wc_gateway_cashway'
	end

	it 'configures module' do
    fill_in 'woocommerce_woocashway_cashway_login', :with => ENV['API_KEY']
    fill_in 'woocommerce_woocashway_cashway_password', :with => ENV['API_SECRET']
    find(:xpath, '//input[@value="Save changes"]').click
	end
end
