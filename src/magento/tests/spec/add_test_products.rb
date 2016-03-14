require 'spec_helper'

describe "Adds test products" do
  it "goes to admin" do
    session.visit ENV['ADMIN_PATH']
  end

  it "authenticates" do
    find('#username').set ENV['ADMIN_USERNAME']
    find('#login').set ENV['ADMIN_PASSWD']
    first('.form-button').click
  end

  it "goes in catalog" do
    if page.first('#message-popup-window')
      find(:xpath, '//a[@title = "close"]').click
    end
    find(:xpath, '//ul[@id="nav"]/li/a/span[text()="Catalog"]').click
  end

  it "goes to Manage Products" do
    find(:xpath, '//ul[@id="nav"]/li[3]/ul/li/a/span[text()="Manage Products"]').click
  end

  [50, 250, 2500].each do |price|
    describe "adds #{price} € product" do
      it "adds product" do
        find(:xpath, '//button[@title="Add Product"]').click
        find(:xpath, '//span[@id="continue_button"]/button[@title="Continue"]').click

        fill_in 'name', with: "test_#{price}"
        fill_in 'description', with: "test product of cost #{price}"
        fill_in 'short_description', with: "short desc for #{price} € test product"
        fill_in 'sku', with: rand(2000).to_s
        fill_in 'weight', with: '250'

        find(:xpath, '//select[@id="status"]/option[@value="1"]').click
        find(:xpath, '//select[@id="visibility"]/option[@value="4"]').click
        find(:xpath, '//button[@title="Save"]').click

        fill_in 'price', with: price.to_s
        find(:xpath, '//select[@id="tax_class_id"]/option[@value="0"]').click
        find(:xpath, '//a[@id="product_info_tabs_inventory"]').click
        fill_in 'inventory_qty', with: '100000'
        find(:xpath, '//select[@id="inventory_stock_availability"]/option[@value="1"]').click
        find(:xpath, '//button[@title="Save"]').click
      end
    end
  end
end