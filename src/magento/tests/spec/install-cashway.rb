require 'spec_helper'
require 'pathname'

MODULE_CONNECT_ACTION_NAME =ENV['MODULE_CHANNEL'] + '|' + ENV['MODULE_NAME']

describe "resets Cashway module" do

it "loads Magento connect manager" do
  session.visit ENV['ADMIN_MANAGER_PATH']
end

it "logs in" do
  find('#username').set ENV['ADMIN_USERNAME']
  find('#password').set ENV['ADMIN_PASSWD']
  first('button').click
  #Check if we are logged
  expect(page).to have_selector '#install_package_id'
end

it "puts not store in maintenance mode" do
  find(:css, '#maintenance').set(false)
end

it "uninstalls module if present" do
  #Skip uninstall if cashway module is not installed
  skip "CashWay module is not installed." unless page.has_content? ENV['MODULE_NAME']
  #Else select uninstall in Cashway's select tag
  page.select 'Uninstall', from: "actions[" + MODULE_CONNECT_ACTION_NAME + "]"
  #Update change
  first(:xpath, "//button[text() = 'Commit Changes']").click
  sleep 5 #because uninstall is run in ajax and we should wait for cleaning cache
end

it "uploads new module version" do
  #Archive Pathname from .env
  archive = Pathname.new ENV['MODULE_ARCHIVE']
  #attach file to form
  attach_file 'file', archive.realpath
  #Upload and install package
  find(:xpath, '//button[text()="Upload"]').click
  sleep 5 #because install is run in ajax and we should wait for cleaning cache
end

it "goes back to admin" do
  click_link 'Return to Admin' #Click on link Return Admin is used instead session.visit ENV['ADMIN_PATH'] because it use SID form sessio connection
  expect(page).to have_selector '#page-help-link'

  #Force locale en_US
  locale_label = find(:xpath,'//option[@value="en_US"]').text
  page.select locale_label, from: 'interface_locale' unless find('#interface_locale').find('option[selected]').value == "en_US"
end

it "Logs in and out in order to be able to get the Cashway payment configuration" do
    find(:xpath, '//a[@class="link-logout"]').click
    find('#username').set ENV['ADMIN_USERNAME']
    find('#login').set ENV['ADMIN_PASSWD']
    first('input.form-button').click
    if page.first('#message-popup-window-mask')
      find(:xpath, '//a[@title="close"]').click
    end
    expect(page).to have_selector '.head-dashboard'
end

it "goes to System > Configuration > Sales > CashWay payment" do
  #Go to system=>configuration
  if Capybara.current_driver == :poltergeist
    find(:xpath, '//span[text()="System"]').trigger('click')
    find(:xpath, '//span[text()="Configuration"]').trigger('click') #Do full screen (or large size) on firefox for it's work :-(
  else
    find(:xpath, '//span[text()="System"]').hover
    find(:xpath, '//span[text()="Configuration"]').click #Do full screen (or large size) on firefox for it's work :-(
  end

  #And select cashway configuration
  find(:xpath, '//span[normalize-space(text())="CashWay payment"]').click
  find(:xpath, '//a[@id="cashway_cashway_api-head"]').click unless find('#cashway_cashway_api-state', visible: false).value == "1" #Expand form header if is not

  #Fill test credentials
  fill_in 'cashway_cashway_api_api_key_test', with: ENV['API_KEY']
  fill_in 'cashway_cashway_api_api_secret_test', with: ENV['API_SECRET']
  fill_in 'cashway_cashway_api_api_shared_secret_test', with: ENV['API_SHARED_SECRET']
  first(:xpath, '//button[@title="Save Config"]').click
end

it "goes to Payment Methods and configures CashWay" do
  #Go to payment methods configuration
  find(:xpath, '//span[normalize-space(text())="Payment Methods"]').click
  #Expand form header if is not
  find(:xpath, '//a[@id="payment_cashway-head"]').click unless find('#payment_cashway-state', visible: false).value == "1"
  #Fill configuration form
  select 'Yes', from: 'payment_cashway_active'
  select 'Yes', from: 'payment_cashway_allowredirect'
  select 'All Enabled Methods', from: 'payment_cashway_redirectspecific'
  select 'Yes', from: 'payment_cashway_debug'
  select 'Yes', from: 'payment_cashway_is_test_mode'
  #Save configuration
  first(:xpath, '//button[@title="Save Config"]').click
end

end
