require 'spec_helper'

describe "Adds test products (250/2500 €)" do
  it "loads admin page" do
		session.visit ENV['LOGIN_PATH']
	end

  it "authenticates" do
    find('#user_login').set ENV['ADMIN_LASTNAME']
    find('#user_pass').set ENV['ADMIN_PASSWD']
    find('#wp-submit').click
  end

  [50, 250, 2500].each do |price|
    it "adds #{price} € product" do
      find('#menu-posts-product').click
      find(:xpath, "//a[@href='post-new.php?post_type=product']").click
      fill_in 'title', :with => 'Test ' + price.to_s
      fill_in '_regular_price', :with => price.to_s
      find(:xpath, '//input[@id="_virtual"]').click
      find(:xpath, '//input[@id="publish"]').click
      sleep 1
    end
  end
end
