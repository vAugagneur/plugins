require 'spec_helper'

describe "Admin post configuration" do

  it "authenticates" do
    session.visit '/admin'
    session.visit ENV['ADMIN_PATH'] if page.has_content?('NOT AVAILABLE')

    fill_in 'email', with: ENV['ADMIN_EMAIL']
    fill_in 'passwd', with: ENV['ADMIN_PASSWD']
    find(:xpath, '//button[@class="btn btn-primary btn-lg btn-block ladda-button"]').click
  end

  it "sets filter country" do
    find('li#maintab-AdminParentLocalization').hover
    find(:xpath, '//li[@id="subtab-AdminCountries"]/a').click
    fill_in 'countryFilter_b!name', with: ENV['SERVER_COUNTRY']
    find(:xpath, '//button[@id="submitFilterButtoncountry"]').click

    unless find(:xpath, '//a[@class="list-action-enable action-enabled"]')
      find(:xpath, '//a[@class="list-action-enable action-disabled"]').click
    end
  end

  # TODO: Advanced Parameters > Performance :
  # TODO: - Recompile templates if the files have been updated
  # TODO: - No cache
end
