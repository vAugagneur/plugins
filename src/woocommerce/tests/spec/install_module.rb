require 'spec_helper'

MODULE_ANCHOR='anchor' + ENV['MODULE_NAME'].downcase.capitalize

describe "Install CashWay module on WordPress: " + ENV['TEST_SERVER'] do

	it "loads admin page" do
		session.visit ENV['LOGIN_PATH']
	end

  it "authenticates" do
    find('#user_login').set ENV['ADMIN_LASTNAME']
    find('#user_pass').set ENV['ADMIN_PASSWD']
    find('#wp-submit').click
  end

  it 'check if Cashway is already installed' do
			find('#menu-plugins').click
	    find(:xpath, "//a[@href='plugins.php']").click
      if page.first('#woocommerce-cashway')

        find(:xpath, '//a[@aria-label="Deactivate WooCommerce CashWay"]').click
        find(:xpath, '//a[@aria-label="Delete WooCommerce CashWay"]').click
        first(:xpath, '//input[@id="submit"]').click
      end
  end

	it 'goes to modules list' do
		find('#menu-plugins').click
    find(:xpath, "//a[@href='plugin-install.php']").click
		first(:xpath, "//a[contains(@href, 'plugin-install.php?tab=upload')]").click
		page.all('input[id="pluginzip"]').first.set File.absolute_path(ENV['MODULE_ARCHIVE'])
    click_button 'install-plugin-submit'
    first(:xpath, '//a[@target="_parent"]').click
		find('#toplevel_page_woocommerce').click
		find(:xpath, "//a[@href='admin.php?page=wc-settings']").click
		find(:xpath, "//a[contains(@href, 'admin.php?page=wc-settings&tab=checkout')]").click
		first(:xpath, "//a[contains(@href, 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_cashway')]").click
	end

	it 'configures module' do
    fill_in 'woocommerce_woocashway_cashway_login', :with => ENV['API_KEY']
    fill_in 'woocommerce_woocashway_cashway_password', :with => ENV['API_SECRET']
    find(:xpath, '//input[@value="Save changes"]').click
	end
end
