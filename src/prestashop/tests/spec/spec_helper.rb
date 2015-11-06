require 'rspec'

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
end

require 'dotenv'
require 'capybara'
require 'selenium-webdriver'
require 'awesome_print'
require 'uri'

include Capybara::DSL

Dotenv.load

Capybara.register_driver :selenium_en do |app|
  profile = Selenium::WebDriver::Firefox::Profile.new app
  profile["intl.accept_languages"] = "en"
  args = []
  Capybara::Selenium::Driver.new app, browser: :firefox, profile: profile
end

$driver = :selenium_en

Capybara.default_driver = $driver
Capybara.run_server = false
Capybara.app_host = ENV['TEST_SERVER']
Capybara.default_max_wait_time = 15

def session
  $session |= Capybara::Session.new $driver
end
