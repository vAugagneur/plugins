require 'rspec'
require 'dotenv'
require 'capybara'
require 'selenium-webdriver'
require 'capybara-webkit'
require 'capybara/poltergeist'
require 'capybara-screenshot/rspec'
require 'awesome_print'
require 'uri'

RSpec.configure do |config|
  config.expect_with :rspec do |expectations|
    expectations.include_chain_clauses_in_custom_matcher_descriptions = true
  end

  config.mock_with :rspec do |mocks|
    mocks.verify_partial_doubles = true
  end

	config.fail_fast = true
  #config.disable_monkey_patching!
  #config.warnings = true
  config.default_formatter = 'doc'
  config.profile_examples = 10
	config.order = :defined

  #config.include Capybara::DSL, type: :feature
end

include Capybara::DSL

Dotenv.load

Capybara.register_driver :selenium_en do |app|
  profile = Selenium::WebDriver::Firefox::Profile.new app
  profile["intl.accept_languages"] = "en"
  args = []
  Capybara::Selenium::Driver.new app, browser: :firefox, profile: profile
end

$driver = :selenium_en
#$driver = :webkit
#$driver = :poltergeist

Capybara::Webkit.configure do |config|

  config.block_unknown_urls
  config.skip_image_loading
end


Capybara.default_driver = $driver
Capybara.run_server = false
Capybara.app_host = ENV['TEST_SERVER']
Capybara.default_max_wait_time = 15

Capybara::Screenshot.prune_strategy = :keep_last_run

def session
  $session |= Capybara::Session.new $driver
end

# If Webkit
#page.driver.header 'Accept-Language', 'en'

# If PhantomJS
#page.driver.add_header('Accept-Language', 'en', permanent: true)

# If Selenium
#page.driver.browser.manage.window.maximize
