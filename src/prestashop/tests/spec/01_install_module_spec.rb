require 'spec_helper'

MODULE_ANCHOR='anchor' + ENV['MODULE_NAME'].downcase.capitalize

describe "Delete + install of CashWay module on PrestaShop: " + ENV['TEST_SERVER'] do

	it "loads admin page" do
		session.visit ENV['ADMIN_PATH'] + '/index.php?controller=AdminModules'
	end

	it "authenticates" do
		find('#email').set ENV['ADMIN_EMAIL']
		find('#passwd').set ENV['ADMIN_PASSWD']
		find('label[for=stay_logged_in]').click
		find('button[name=submitLogin]').click
		expect(page).to have_content 'Me' #ENV['ADMIN_NAME']
	end

	it 'goes to modules list' do
#		find('li#maintab-AdminParentModules').find('a.title').click
	end

	it 'checks if module is already there' do
		find('#moduleQuicksearch').set ENV['MODULE_NAME']
	end

	it 'removes installed module' do
		skip "CashWay module is not installed." unless page.has_selector? '#' + MODULE_ANCHOR

		page.execute_script("window.scrollTo(0,1000);")
		expect(page).to have_selector '#' + MODULE_ANCHOR
		find(:xpath, '//div[@id="anchorCashway"]/../../td[@class="actions"]/div/div/button[@data-toggle="dropdown"]').click
		click_link 'Delete'
		page.driver.browser.switch_to.alert.accept
		sleep(5)
		expect(page).to have_content 'Module deleted successfully.'
	end

	it 'uploads a new version of the module' do
		click_link 'Add a new module'
		expect(page).to have_content 'ADD A NEW MODULE'
		page.execute_script('document.getElementById("file").removeAttribute("class");')
		page.all('input[id="file"]', visible: false).first.set File.absolute_path(ENV['MODULE_ARCHIVE'])
		page.execute_script("window.scrollTo(0,800);")
		click_button 'Upload this module'
		expect(page).to have_content 'The module was successfully downloaded.'
	end

	it 'installs module' do
		find('#moduleQuicksearch').set ENV['MODULE_NAME']
		fail "Le module n'est pas là..." unless page.has_selector? '#' + MODULE_ANCHOR

		page.execute_script("window.scrollTo(0,1000);")
		click_link 'Install'
		expect(page).to have_content 'Module(s) installed successfully.'
	end

	# Go query PrestaShop configuration value in vagrant test box
	def get_shared_secret
		puts Dir.pwd
		cmd = 'cd ../../../tests/box; \
			vagrant ssh -c \
				"mysql -uroot -sNe \
					\"SELECT value FROM ps_configuration WHERE name=\'CASHWAY_SHARED_SECRET\';\" \
					prestashop"'

		data = []
		IO.popen(cmd) { |f| data << f.gets }

		data[0].strip
	end

	# Update shared secret in .env
	def update_env_shared_secret_with(value)
		puts Dir.pwd
		system("sed -i .bak 's|SHARED_SECRET=.*|SHARED_SECRET=#{value}|g' tests/.env")
	end

	# Only valid/useful if using vagrant here:
	# This is necessary for client_use.rb payment tests
	it 'fetches shared secret on test host' do
		update_env_shared_secret_with get_shared_secret
	end

	it 'configures module' do
		find('#CASHWAY_API_KEY').set ENV['API_KEY']
		find('#CASHWAY_API_SECRET').set ENV['API_SECRET']
		click_button 'configuration_form_submit_btn'
		expect(page).to have_content 'API key updated.'
		expect(page).to have_content 'API secret updated.'
	end
end
