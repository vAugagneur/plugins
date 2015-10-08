require 'spec_helper'

describe "Admin post configuration" do

  it "authenticates" do
    session.visit ENV['ADMIN_PATH']
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
end
