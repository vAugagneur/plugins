require 'spec_helper'

MODULE_ANCHOR='anchor' + ENV['MODULE_NAME'].downcase.capitalize

describe "Mise à jour du module CashWay sur PrestaShop " + ENV['TEST_SERVER'] do

	it "charge la page d'admin" do
		session.visit ENV['ADMIN_PATH']
		#expect(page).to have_content 'Linux'
	end

	it "s'identifie" do
		find('#email').set ENV['ADMIN_EMAIL']
		find('#passwd').set ENV['ADMIN_PASSWD']
		find('label[for=stay_logged_in]').click
		find('button[name=submitLogin]').click
		expect(page).to have_content ENV['ADMIN_NAME']
	end

	it 'va dans la liste des modules' do
		find('li#maintab-AdminParentModules').find('a.title').click
	end

	it 'vérifie si le module est là' do
		find('#moduleQuicksearch').set ENV['MODULE_NAME']
		fail "Le module n'est pas installé" unless page.has_selector? '#' + MODULE_ANCHOR
	end

	it 'charge une nouvelle archive du module' do
		click_link 'Ajouter un nouveau module'
		expect(page).to have_content 'AJOUTER UN NOUVEAU MODULE'
		page.execute_script('$("#file").removeClass("hide");')
		page.all('input[id="file"]', visible: false).first.set File.absolute_path(ENV['MODULE_ARCHIVE'])
		click_button 'Charger le module'
		expect(page).to have_content 'Le module a bien été téléchargé.'
	end
end
