require 'spec_helper'

describe "PrestaShop basic setup" do

  it "loads installation page & select English" do
    session.visit '/install'
    find('#langList').find('option[value="en"]').click
    find('#btNext').click
  end

  it "validates license" do
    find('#set_license').click
    find('#btNext').click
  end

  it "fills admin user form" do
    fill_in 'infosShop', :with => 'test'
    fill_in 'infosFirstname', :with => ENV['ADMIN_FIRSTNAME']
    fill_in 'infosName', :with => ENV['ADMIN_LASTNAME']
    fill_in 'infosEmail', :with => ENV['ADMIN_EMAIL']
    fill_in 'infosPassword', :with => ENV['ADMIN_PASSWD']
    fill_in 'infosPasswordRepeat', :with => ENV['ADMIN_PASSWD']
    find('#btNext').click
  end

  it "configures database" do
    find('#btNext').click
  end

  it "succeeds installation" do
    #$stdin.gets
    sleep 70
    find('#install_process_success', visible: true)
  end
end
