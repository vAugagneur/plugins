require 'spec_helper'

describe "Adds test products" do
  it "goes to admin" do
    session.visit ENV['ADMIN_PATH']
  end

  it "authenticates" do
    find('#passwd').set ENV['ADMIN_PASSWD']
    find('#email').set ENV['ADMIN_EMAIL']
    find('button[name=submitLogin]').click
  end

  # NOTE: do not attempt to bulk upload a CSV products file.
  # Their file upload widget is not accessible.
  # If you still do, remember to increment below counter.
  # Number of times it has been tried and given up: 2.

  [50, 250, 2500].each do |price|
    describe "adds #{price} € product" do
      it "open new product page" do
        find('li#maintab-AdminCatalog').click
        click_link_or_button 'Add new product'

        find(:xpath, '//input[@id="virtual_product"]').click
        fill_in 'name_1', with: 'Test ' + price.to_s

        find('#link-Prices').click
        fill_in 'priceTI', with: price.to_s

        should have_xpath("//button[@name='submitAddproductAndStay' and not(@disabled='disabled')]")
        find('button[name=submitAddproductAndStay]').click

        find('#link-Quantities').click
        should have_selector('td#qty_0')
        fill_in 'qty_0', with: '10000'

        should have_xpath("//button[@name='submitAddproductAndStay' and not(@disabled='disabled')]")
        find('button[name=submitAddproductAndStay]', match: :first).click

        should have_content 'Successful update'
      end
    end
  end

  it "?" do
    find('li#maintab-AdminCatalog').find('a.title').click
  end
end
