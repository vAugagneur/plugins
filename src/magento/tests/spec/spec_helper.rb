require 'rspec'
require 'dotenv'
require 'capybara'
require 'capybara/dsl'
require 'selenium-webdriver'
require 'awesome_print'
require 'capybara/poltergeist'
require 'capybara/webkit'
require 'uri'

def define_driver (driver_name)
  case driver_name
  when 'selenium'
    puts "The selected driver is Selenium."
    $driver = :selenium_en
  when 'webkit'
    puts "The selected driver is Webkit."
    $driver = :webkit
  else
    puts "The default driver is Poltergeist"
    $driver = :poltergeist
  end
end

define_driver ENV['DRIVER']

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

include Capybara::DSL

Dotenv.load

Capybara.register_driver :selenium_en do |app|
  profile = Selenium::WebDriver::Firefox::Profile.new app
  profile["intl.accept_languages"] = "en"
  profile["webdriver.load.strategy"] = "unstable"
  args = []
  Capybara::Selenium::Driver.new app, browser: :firefox, profile: profile
end

Capybara.default_driver = $driver
Capybara.run_server = false
Capybara.app_host = ENV['TEST_SERVER']
Capybara.default_max_wait_time = 15

def session
  $session |= Capybara::Session.new $driver
end

if $driver == :webkit
  page.driver.header 'Accept-Language', 'en'
end

if $driver == :poltergeist
  page.driver.add_header('Accept-Language', 'en', permanent: true)
end

# If Selenium
#page.driver.browser.manage.window.maximize
