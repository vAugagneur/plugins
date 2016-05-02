require 'spec_helper'

MODULE_ANCHOR='anchor' + ENV['MODULE_NAME'].downcase.capitalize

describe "Update of the Prestashop CASHWAY module " + ENV['TEST_SERVER'] do

	it "loads admin page" do
		session.visit ENV['ADMIN_PATH']
		#expect(page).to have_content 'Linux'
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
		fail "The module is not installed" unless page.has_selector? '#' + MODULE_ANCHOR
	end

	it 'loads a new archive of the module' do
		click_link 'Add a new module'
		expect(page).to have_content 'ADD A NEW MODULE'
		page.execute_script('$("#file").removeClass("hide");')
		page.all('input[id="file"]', visible: false).first.set File.absolute_path(ENV['MODULE_ARCHIVE'])
		page.execute_script("window.scrollTo(0,800);")
		click_button 'Upload this module'
		expect(page).to have_content 'The module was successfully downloaded.'
	end
end
