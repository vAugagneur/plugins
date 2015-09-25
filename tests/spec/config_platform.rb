require 'spec_helper'

describe "Installation de la boutique PrestaShop " do

  it "charge la page d'admin" do
    session.visit 'http://localhost:8082/prestashop/install'
    find('#btNext').click
    find('#set_license').click
    find('#btNext').click
    fill_in 'infosShop', :with => 'test'
    fill_in 'infosFirstname', :with => 'Jean-Baptiste'
    fill_in 'infosName', :with => 'Pacaud-Paris'
    fill_in 'infosEmail', :with => 'testpacaud@gmail.com'
    fill_in 'infosPassword', :with => 'cashwaytest'
    fill_in 'infosPasswordRepeat', :with => 'cashwaytest'
    find('#btNext').click
    find('#btNext').click
    $stdin.gets
  end


end
