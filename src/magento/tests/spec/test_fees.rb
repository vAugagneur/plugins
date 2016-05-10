require 'spec_helper'

describe "Test the right calculation of the fees" do

  #Don't load the Paypal image which blocks the Poltergeist tests
  if Capybara.current_driver === :poltergeist
    page.driver.browser.url_blacklist = ["https://www.paypalobjects.com/webstatic/mktg/logo/bdg_now_accepting_pp_2line_w.png"]
  end

  it "Logs in to the customer's account" do
    session.visit('/')
    find(:xpath, '//a[@data-target-element="#header-account"]').click
    expect(page).to have_content 'Log In'
    find(:xpath, '//a[@title="Log In"]').click
    first('#email').set ENV['CUSTOMER_EMAIL']
    fill_in 'pass', :with => ENV['CUSTOMER_PASSWD']
    find('#send2').click
  end

  it "Orders 1 20 euros product" do
    sleep(1)
    find('#search').set '_20'
    click_link_or_button 'Search'
    first(:xpath, '//button[@class="button btn-cart"]').click
    expect(page).to have_content 'SHOPPING CART'
    first(:xpath, '//button[@title="Proceed to Checkout"]').click
    expect(page).to have_content 'YOUR CHECKOUT PROGRESS'
    find(:xpath, '//button[@onclick="billing.save()"]').click
    sleep(1)
    find(:xpath, '//button[@onclick="shippingMethod.save()"]').click
    sleep(1)
    choose "Cashway payment"
    expect(page).to have_content "A fee of 1"
    visit('/index.php/checkout/cart')
    sleep(1)
    first(:xpath, '//a[@title="Remove Item"]').click
  end

  @expect = 2;

  [2, 4, 6, 10, 15, 17, 19].each do |quantity|
    it "Orders #{quantity} 50 euros product" do
      sleep(1)
      if quantity === 2
        find('#search').set '_50'
        click_link_or_button 'Search'
        first(:xpath, '//button[@class="button btn-cart"]').click
        expect(page).to have_content 'SHOPPING CART'
        first(:xpath, '//input[@title="Qty"]').click
        first(:xpath, '//input[@title="Qty"]').set '2'
        sleep(2)
        find(:xpath, '//button[@title="Update"]').click
      end
      sleep(1)
      first(:xpath, '//button[@title="Proceed to Checkout"]').click
      expect(page).to have_content 'YOUR CHECKOUT PROGRESS'
      find(:xpath, '//button[@onclick="billing.save()"]').click
      sleep(1)
      find(:xpath, '//button[@onclick="shippingMethod.save()"]').click
      sleep(1)
      choose "Cashway payment"
      expect(page).to have_content "A fee of "+@expect.to_s
      visit('/index.php/checkout/cart')
      sleep(1)
      first(:xpath, '//input[@title="Qty"]').click
      first(:xpath, '//input[@title="Qty"]').set quantity
      sleep(2)
      find(:xpath, '//button[@title="Update"]').click
      if quantity === 19
        sleep(1)
        first(:xpath, '//a[@title="Remove Item"]').click
      end
    end
    @expect = @expect + 1
  end
end
