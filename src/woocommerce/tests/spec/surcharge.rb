require 'spec_helper'

describe "Tests on the surcharge method (are they accurate ?)" do

  it "Test with a cart <= 50 euros" do
    session.visit '/?s=50'
    first(:xpath, '//a[text()="Test 50"]').click
    first(:xpath, '//button[@class="single_add_to_cart_button button alt"]').click
    find(:xpath, '//a[text()="View Cart"]').click
    find(:xpath, '//a[@class="checkout-button button alt wc-forward"]').click
    expect(page).to have_content 'Frais Supplémentaires : 1€'
    session.visit '/?page_id=5'
    first(:xpath, '//a[@class="remove"]').click
  end

  it "Test with a cart <= 150 euros" do
    session.visit '/?s=50'
    first(:xpath, '//a[text()="Test 50"]').click
    [0,1,2].each do
      first(:xpath, '//button[@class="single_add_to_cart_button button alt"]').click
    end
    find(:xpath, '//a[text()="View Cart"]').click
    find(:xpath, '//a[@class="checkout-button button alt wc-forward"]').click
    expect(page).to have_content 'Frais Supplémentaires : 2€'
    session.visit '/?page_id=5'
    first(:xpath, '//a[@class="remove"]').click
  end

  it "Test with a cart <= 250 euros" do
    session.visit '/?s=50'
    # Scroll to prevent the product from not being displayed
    page.execute_script("window.scrollTo(0,250)")
    first(:xpath, '//a[text()="Test 250"]').click
    first(:xpath, '//button[@class="single_add_to_cart_button button alt"]').click
    find(:xpath, '//a[text()="View Cart"]').click
    find(:xpath, '//a[@class="checkout-button button alt wc-forward"]').click
    expect(page).to have_content 'Frais Supplémentaires : 3€'
    session.visit '/?page_id=5'
    first(:xpath, '//a[@class="remove"]').click
  end

  it "Test with a cart > 250 euros" do
    session.visit '/?s=50'
    # Scroll to prevent the product from not being displayed
    page.execute_script("window.scrollTo(0,250)")
    first(:xpath, '//a[text()="Test 250"]').click
    [0,1].each do
      first(:xpath, '//button[@class="single_add_to_cart_button button alt"]').click
    end
    find(:xpath, '//a[text()="View Cart"]').click
    find(:xpath, '//a[@class="checkout-button button alt wc-forward"]').click
    expect(page).to have_content 'Frais Supplémentaires : 4€'
    session.visit '/?page_id=5'
    first(:xpath, '//a[@class="remove"]').click
  end
end
