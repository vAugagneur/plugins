require 'spec_helper'
require 'pathname'

describe "Magento configuration" do

it "Load admin page" do
  session.visit ENV['ADMIN_PATH']
end

it "Log in" do
  find('#login').set ENV['ADMIN_PASSWD']
  find('#username').set ENV['ADMIN_FIRSTNAME']
  first('.form-button').click
end

it "Connect to Magentoconnect" do
  if page.first('#message-popup-window')
    find(:xpath, '//a[@title = "close"]').click
  end
  find(:xpath, '//span[text()="System"]').click
  find(:xpath, '//span[text()="Magento Connect"]').click
  find(:xpath, '//span[text()="Magento Connect Manager"]').click
  find('#password').set ENV['ADMIN_PASSWD']
  find('#username').set ENV['ADMIN_FIRSTNAME']
  find(:xpath, '//button[@type="submit"]').click
  archive = Pathname.new ENV['MODULE_ARCHIVE']
  attach_file 'file', archive.realpath
  find(:xpath, '//button[text()="Upload"]').click
end

it "Active Cashway" do
  session.visit ENV['ADMIN_PATH']
  find(:xpath, '//span[text()="System"]').click
  find(:xpath, '//span[text()="Configuration"]').click
  find(:xpath, '//span[normalize-space(text())="Cashway"]').click
  find(:xpath, '//a[@id="cashway_cashway_api-head"]').click
  fill_in 'cashway_cashway_api_api_key_test', :with => ENV['API_KEY']
  fill_in 'cashway_cashway_api_api_secret_test', :with => ENV['API_SECRET']
  find(:xpath, '//button[@title="Save Config"]').click
end

end
