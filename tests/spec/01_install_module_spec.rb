require 'spec_helper'

MODULE_ANCHOR='anchor' + ENV['MODULE_NAME'].downcase.capitalize

describe "Delete + install of CashWay module on PrestaShop: " + ENV['TEST_SERVER'] do

	it "loads admin page" do
		session.visit ENV['ADMIN_PATH']
	end

	it "authenticates" do
		find('#email').set ENV['ADMIN_EMAIL']
		find('#passwd').set ENV['ADMIN_PASSWD']
		find('label[for=stay_logged_in]').click
		find('button[name=submitLogin]').click
		expect(page).to have_content ENV['ADMIN_NAME']
	end

	it 'goes to modules list' do
		find('li#maintab-AdminParentModules').find('a.title').click
	end

	it 'checks if module is already there' do
		find('#moduleQuicksearch').set ENV['MODULE_NAME']
	end

	it 'removes installed module' do
		skip "CashWay module is not installed." unless page.has_selector? '#' + MODULE_ANCHOR

		find(:xpath, '//*[@id="' + MODULE_ANCHOR + '"]/../../td[4]/div/div/button').click
		click_link 'Delete'
		page.driver.browser.switch_to.alert.accept
		expect(page).to have_content 'Module deleted successfully.'
	end

	it 'uploads a new version of the module' do
		click_link 'Add a new module'
		expect(page).to have_content 'ADD A NEW MODULE'
		page.execute_script('$("#file").removeClass("hide");')
		page.all('input[id="file"]', visible: false).first.set File.absolute_path(ENV['MODULE_ARCHIVE'])
		click_button 'Upload this module'
		expect(page).to have_content 'The module was successfully downloaded.'
	end

	it 'installs module' do
		find('#moduleQuicksearch').set ENV['MODULE_NAME']
		fail "Le module n'est pas là..." unless page.has_selector? '#' + MODULE_ANCHOR
		click_link "Install"
		click_link "Proceed with the installation"
		expect(page).to have_content 'Module(s) installed successfully.'
	end

	it 'configures module' do
		find('#CASHWAY_API_KEY').set ENV['API_KEY']
		find('#CASHWAY_API_SECRET').set ENV['API_SECRET']
		click_button 'Save'
		expect(page).to have_content 'API key updated.'
		expect(page).to have_content 'API secret updated.'
	end
end
