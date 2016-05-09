require 'spec_helper'

describe "Set shipping price" do
  it "Charge la page admin" do
    session.visit ENV['ADMIN_PATH']
  end

  it "S'identifie" do
    find('#username').set ENV['ADMIN_USERNAME']
    find('#login').set ENV['ADMIN_PASSWD']
    first('.form-button').click
  end

  it "set shipping price" do
    sleep(1)
    find(:xpath, '//a[@title="close"]').click unless !page.first('#message-popup-window-mask')
    find(:xpath, '//span[text()="System"]').click
    find(:xpath, '//span[text()="Configuration"]').click
    find(:xpath, '//span[normalize-space(text())="Shipping Methods"]').click
    expect(page).to have_content("Flat Rate")
    find('#carriers_flatrate-head').click
    find(:xpath, '//input[@id="carriers_flatrate_price"]').set '0'
    find(:xpath, '//button[@class="scalable save"]').click
    sleep(3);
    end
end
