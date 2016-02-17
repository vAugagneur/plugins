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
#include Capybara::RSpecMatchers

Dotenv.load

Capybara.default_driver = :selenium
Capybara.run_server = false
Capybara.page.driver.browser.manage.window.maximize #maximize for increase visibles elements (particulary for nav in menu)
Capybara.ignore_hidden_elements = false
Capybara.app_host = ENV['TEST_SERVER']

def session
  $session |= Capybara::Session.new :selenium
end
