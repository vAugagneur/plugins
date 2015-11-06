require 'spec_helper'

describe "Adds test products (50/250/2500 €)" do
  it "goes to admin" do
    session.visit ENV['ADMIN_PATH']
  end

  it "authenticates" do
    find('#passwd').set ENV['ADMIN_PASSWD']
    find('#email').set ENV['ADMIN_EMAIL']
    find('button[name=submitLogin]').click
  end

  [50, 250, 2500].each do |price|
    it "adds #{price} € product" do
      find('li#maintab-AdminCatalog').find('a.title').click
      click_link_or_button('desc-product-new')
      fill_in 'name_1', :with => 'Test ' + price.to_s
      find(:xpath, '//input[@id="virtual_product"]').click
      click_link_or_button('link-Prices')
      fill_in 'priceTE', :with => price.to_s

      should have_xpath("//button[@name='submitAddproductAndStay' and not(@disabled='disabled')]")
      find('button[name=submitAddproductAndStay]').click
    end

    it "adds quantities" do
      click_link_or_button('link-Quantities')
      should have_selector('td#qty_0')
      fill_in 'qty_0', :with => '10000'

      should have_xpath("//button[@name='submitAddproductAndStay' and not(@disabled='disabled')]")
      find('button[name=submitAddproductAndStay]', match: :first).click
      should have_content 'Successful update'
    end
  end

  it "?" do
    find('li#maintab-AdminCatalog').find('a.title').click
  end
end
