require 'spec_helper'

describe "Logging in" do
  it "authenticates" do
    session.visit '/index.php?controller=my-account'
    find('#email').set ENV['CUSTOMER_EMAIL']
    find('#passwd').set ENV['CUSTOMER_PASSWD']
    find_button('SubmitLogin').click
    expect(page).to have_content ENV['CUSTOMER_NAME']
  end
end

[50, 250, 2500].each do |price|

  describe "Testing ordering something > #{price} € on #{ENV['TEST_SERVER']}" do
    it "adds a €#{price}+ product to cart" do
      session.visit '/'
      should have_content 'Test ' + price.to_s
      selector = '//a[@class="product-name" and @title="Test ' + price.to_s + '"]/../..'
      #find(:xpath, selector, match: :first).hover
      #click_link_or_button('Add to cart')
      find(:xpath, selector, match: :first).click_link_or_button('Add to cart')

      should have_content 'Proceed to checkout'
    end

    it "checks the cart out" do
      visit '/index.php?controller=order'
      find('a', text: 'Proceed to checkout').click
    end

    it "checks shipping/billing addresses" do
      find("button[name=processAddress]").click
      expect(page).to have_content('terms of service')
    end

    it "accepts terms & conditions, confirms order" do
      expect(page).to have_selector('#uniform-cgv')
      find('label[for=cgv]').click
      find(:xpath, '//button[@class="button btn btn-default standard-checkout button-medium"]').click
      expect(page).to have_content ENV['MODULE_PAY_ACTION_TEXT']
      expect(page).to have_content 'Continue shopping'
      expect(page).to have_content 'CHOOSE YOUR PAYMENT METHOD'
    end

    it "selects our #{ENV['MODULE_NAME']} payment solution" do
      find('a.' + ENV['MODULE_NAME'].downcase).click
    end

    if 2500 == price
      it "confirms that is is blocked" do
        expect(page).to have_content 'Hélas, vous avez dépassé le montant maximum possible d\'achats via CashWay sur la période des 12 derniers mois (plus d’informations).'
      end
    end

    if [250, 50].include? price

      it "confirms CashWay transaction" do
        fail "Le service ne fonctionne pas..." if page.has_content? 'Hélas'

        expect(page).to have_content 'Total à payer au buraliste'
        expect(page).to have_content 'Please confirm your order by clicking'
        expect(page).to have_content 'avoir lu et adhéré sans réserve aux conditions générales de CashWay'
        expect(page).to have_content 'Les distributeurs proches de chez vous'

        if 250 == price
          expect(page).to have_content 'pour encaisser ce montant, la réglementation française nous impose de contrôler votre identité.'
        end

        find('#cashway-confirm-btn').click
      end

      it "checks what to do next" do
        expect(page).to have_content 'rendez-vous dans un des points de paiement indiqués sur notre carte, muni du code suivant'
        expect(page).to have_content 'Please note and keep your order reference'
        $barcode = find('#cashway-barcode-label').text.gsub(' ', '')

        if 250 == price
          mail = URI.parse(page.find('a[id = "cashway-kyc-email"]')['href'])
          expect(mail.to).to eq 'validation@cashway.fr'

          form = URI.parse(page.find('a[id = "cashway-kyc-form"]')['href'])
          expect(form.scheme).to eq 'https'
          expect(form.host.end_with?('.cashway.fr')).to be true
        end

        # TODO télécharger/imprimer ticket de paiement
        # TODO liste texte des bureaux de paiement
      end

    end

  end
end
