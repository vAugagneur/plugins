require 'spec_helper'

describe "Installation de la boutique PrestaShop " do

  it "charge la page d'admin" do
    session.visit ENV['ADMIN_PATH']
    find('#btNext').click
    find('#set_license').click
    find('#btNext').click
    fill_in 'infosShop', :with => 'test'
    fill_in 'infosFirstname', :with => ENV['CUSTOMER_FIRSTNAME']
    fill_in 'infosName', :with => ENV['CUSTOMER_LASTNAME']
    fill_in 'infosEmail', :with => ENV['CUSTOMER_EMAIL']
    fill_in 'infosPassword', :with => ENV['CUSTOMER_PASSWD']
    fill_in 'infosPasswordRepeat', :with => ENV['CUSTOMER_PASSWD']
    find('#btNext').click
    find('#btNext').click
  end
end
