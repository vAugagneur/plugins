require 'spec_helper'

describe "Installation de la page admin" do

  it "charge la page d'admin" do
    session.visit ENV['ADMIN_PATH']
    fill_in 'email', :with => ENV['ADMIN_EMAIL']
    fill_in 'passwd', :with => ENV['ADMIN_PASSWD']
    find(:xpath, '//button[@class="btn btn-primary btn-lg btn-block ladda-button"]').click
    find('li#maintab-AdminParentLocalization').click
    find(:xpath, '/html/body/div[1]/div[1]/nav/ul/li[9]/ul/li[4]/a').click
    fill_in 'countryFilter_b!name', :with => ENV['SERVER_COUNTRY']
    find(:xpath, '//button[@id="submitFilterButtoncountry"]').click
    find(:xpath, '/html/body/div[1]/div[2]/div[5]/div/form[2]/div/div[2]/table/tbody/tr/td[7]/a/i[2]').click
  end
end
