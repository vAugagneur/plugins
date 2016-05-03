require 'spec_helper'

describe "PrestaShop basic setup" do

  it "loads installation page & select English" do
    session.visit '/install'
    if Capybara.current_driver === :poltergeist
      find('#langList').find('option[value="en"]').trigger('click')
    else
      find('#langList').find('option[value="en"]').click
    end
    find('#btNext').click
  end

  it "validates license" do
    expect(page).to have_content "License Agreements"
    find('#set_license').click
    find('#btNext').click
  end

  it "fills admin user form" do
    find(:xpath, '//span[text()="Select your country"]').click
    find(:xpath, '//ul[@class="chosen-results"]/li[contains(@class, "active-result") and text()="' + ENV['SERVER_COUNTRY'] + '"]').click

    fill_in 'infosShop', with: 'test'
    fill_in 'infosFirstname', with: ENV['ADMIN_FIRSTNAME']
    fill_in 'infosName', with: ENV['ADMIN_LASTNAME']
    fill_in 'infosEmail', with: ENV['ADMIN_EMAIL']
    fill_in 'infosPassword', with: ENV['ADMIN_PASSWD']
    fill_in 'infosPasswordRepeat', with: ENV['ADMIN_PASSWD']

    sleep(5)

    find('#btNext').click
  end

  it "configures database" do
    expect(page).to have_content "Configure your database"
    find('#btNext').click
  end

  it "succeeds installation" do
    expect(page).to have_content 'finished'
    find('#install_process_success', visible: true)
  end
end
