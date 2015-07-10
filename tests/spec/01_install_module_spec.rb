require 'spec_helper'

MODULE_ANCHOR='anchor' + ENV['MODULE_NAME'].downcase.capitalize

describe "Installation d'un nouveau module CashWay sur PrestaShop " + ENV['TEST_SERVER'] do

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
	end

	it 'supprime le module existant' do
		skip "Le module n'est pas installé" unless page.has_selector? '#' + MODULE_ANCHOR

		find(:xpath, '//*[@id="' + MODULE_ANCHOR + '"]/../../td[4]/div/div/button').click
		click_link 'Supprimer'
		page.driver.browser.switch_to.alert.accept
		expect(page).to have_content 'Module supprimé avec succès.'
	end

	it 'charge une nouvelle archive du module' do
		click_link 'Ajouter un nouveau module'
		expect(page).to have_content 'AJOUTER UN NOUVEAU MODULE'
		page.execute_script('$("#file").removeClass("hide");')
		page.all('input[id="file"]', visible: false).first.set File.absolute_path(ENV['MODULE_ARCHIVE'])
		click_button 'Charger le module'
		expect(page).to have_content 'Le module a bien été téléchargé.'
	end

	it 'installe le module' do
		find('#moduleQuicksearch').set ENV['MODULE_NAME']
		fail "Le module n'est pas là..." unless page.has_selector? '#' + MODULE_ANCHOR
		click_link "Installer"
		click_link "Continuer l'installation"
		expect(page).to have_content 'Module(s) installé(s) avec succès.'
	end

	it 'configure le module' do
		find('#CASHWAY_API_KEY').set 'COUCOU la clé'
		find('#CASHWAY_API_SECRET').set 'COUCOU le secret'
		click_button 'Enregistrer'
		expect(page).to have_content 'Clé mise à jour.'
		expect(page).to have_content 'Secret mis à jour.'
	end
end
