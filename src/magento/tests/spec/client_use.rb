require 'spec_helper'

describe "Tests customer ordering products on Magento on " + ENV['TEST_SERVER'] do

  it "authenticates" do
    visit '/'
    find(:xpath, '//a[@class="skip-link skip-account"]/span[@class="label"]').click
    first(:xpath, '//a[@title="My Account"]').click
    fill_in 'login[username]', with: ENV['CUSTOMER_EMAIL']
    fill_in 'pass', with: ENV['CUSTOMER_PASSWD']
    find(:xpath, '//div[@class="buttons-set"]/button[@type="submit"]').click
    expect(page).to have_content ENV['CUSTOMER_NAME']
  end

  [50, 250, 2500].each do |price|
    describe "testing a #{price} € product" do

      it "searches & adds a product to cart" do
        fill_in 'search', with: "test_#{price}"
        click_link_or_button 'Search'

        first('.product-info').click_link_or_button 'Add to Cart'
      end

      it "checks out" do
        find('.checkout-types.top').click_link_or_button 'Proceed to Checkout'
      end

      it "confirms shipping info" do
        click_link_or_button 'Continue'
        sleep 0.5
      end

      it "confirms shipping method" do
        #click_link_or_button 'Continue' # => does not always work
        find(:xpath, '//button[@onclick="shippingMethod.save()"]').click
      end

      it "selects CashWay payment" do
        choose 'Cashway payment'
        click_link_or_button 'Continue'
        sleep 0.5
      end

      it "places order" do
        click_link_or_button 'Place Order'
      end

      it "checks confirmation page" do
        sleep 3
        expect(page).to have_selector('.checkout-onepage-success')
        save_screenshot("screenshot_#{price}.png")
        expect(page).to have_content('YOUR ORDER HAS BEEN RECEIVED.')
        expect(page).to have_content('You will receive an order confirmation email with details of your order and a link to track its progress.')
      end
    end
  end
end
