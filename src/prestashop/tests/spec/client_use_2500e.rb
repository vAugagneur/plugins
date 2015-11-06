require 'spec_helper'

describe "Test d'une commande > 2500 € sur " + ENV['TEST_SERVER'] do

  it "ajoute un produit > 2500 € au panier" do
    session.visit '/'
    find(:xpath, '//a[@class="product-name" and @title="Test 2500"]/../..', match: :first).click_link_or_button('Add to cart')
    visit '/index.php?controller=order'
  end

  it "passe commande" do
    session.click_link_or_button 'Proceed to checkout'
  end

  it "s'identifie" do
    find('#email').set ENV['CUSTOMER_EMAIL']
    find('#passwd').set ENV['CUSTOMER_PASSWD']
    find_button('SubmitLogin').click
    expect(page).to have_content ENV['CUSTOMER_NAME']
  end

  it "retourne au panier" do
    visit '/index.php?controller=order'
    find('a', text: 'Proceed to checkout').click
  end

  it "passe les adresses" do
    find("button[name=processAddress]").click
    expect(page).to have_content('terms of service')
  end

  it "valide les CGV et confirme" do
    expect(page).to have_selector('#uniform-cgv')
    find('label[for=cgv]').click
    find(:xpath, '//button[@class="button btn btn-default standard-checkout button-medium"]').click
    expect(page).to have_content ENV['MODULE_PAY_ACTION_TEXT']
    expect(page).to have_content 'Continue shopping'
    expect(page).to have_content 'CHOOSE YOUR PAYMENT METHOD'
  end

  it "choisit l'option " + ENV['MODULE_NAME'] do
    find('a.' + ENV['MODULE_NAME'].downcase).click
  end

  it "vérifie l'impossibilité de continuer" do
    expect(page).to have_content 'Hélas, vous avez dépassé le montant maximum possible d\'achats via CashWay sur la période des 12 derniers mois (plus d’informations).'
  end
end
