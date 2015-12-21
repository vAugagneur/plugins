require 'spec_helper'

describe "Admin post configuration" do

  it "authenticates" do
    session.visit '/admin'
    session.visit ENV['ADMIN_PATH'] if page.has_content?('Not Found')

    fill_in 'email', :with => ENV['ADMIN_EMAIL']
    fill_in 'passwd', :with => ENV['ADMIN_PASSWD']
    find(:xpath, '//button[@class="btn btn-primary btn-lg btn-block ladda-button"]').click
  end

  it "goes to localization config page" do
    find('li#maintab-AdminParentLocalization').find('a.title').click
  end

  it "sets default country to '#{ENV['SERVER_COUNTRY']}'" do
    find('#PS_COUNTRY_DEFAULT_chosen').click
    find('#PS_COUNTRY_DEFAULT_chosen .chosen-search input').set(ENV['SERVER_COUNTRY'])
    find('#PS_COUNTRY_DEFAULT_chosen .chosen-results li:nth-child(1)').click
    first('button', text: 'Save').click
  end

  it "sets filter country" do
    find('li#maintab-AdminParentLocalization').click
    find(:xpath, '/html/body/div[1]/div[1]/nav/ul/li[9]/ul/li[4]/a').click
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
