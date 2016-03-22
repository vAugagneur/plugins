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
      expect(page).to have_content('terms of service') if ENV.fetch('VERIFY_CONTENT', 'no') == 'yes'
    end

    it "accepts terms & conditions, confirms order" do
      expect(page).to have_selector('#uniform-cgv')
      find('label[for=cgv]').click
      find(:xpath, '//button[@class="button btn btn-default standard-checkout button-medium"]').click

      if ENV.fetch('VERIFY_CONTENT', 'no') == 'yes'
        expect(page).to have_content ENV['MODULE_PAY_ACTION_TEXT']
        expect(page).to have_content 'Continue shopping'
        expect(page).to have_content 'CHOOSE YOUR PAYMENT METHOD'
      end
    end

    it "selects our #{ENV['MODULE_NAME']} payment solution" do
      find('a.' + ENV['MODULE_NAME'].downcase).click
    end

    if 2500 == price && ENV.fetch('VERIFY_CONTENT', 'no') == 'yes'
      it "confirms that is is blocked" do
        expect(page).to have_content 'Hélas, vous avez dépassé le montant maximum possible d\'achats via CashWay sur la période des 12 derniers mois (plus d’informations).'
      end
    end

    if [250, 50].include? price

      it "confirms CashWay transaction" do
        fail "Le service ne fonctionne pas..." if page.has_content? 'Hélas'

        if ENV.fetch('VERIFY_CONTENT', 'no') == 'yes'
          expect(page).to have_content 'Order total'
          expect(page).to have_content 'Service fees'
          expect(page).to have_content 'Total amount to pay'
          expect(page).to have_content 'you confirm you have read and agreed to'
          expect(page).to have_content 'Dealers near you'

          if 250 == price
            #expect(page).to have_content 'pour encaisser ce montant, la réglementation française nous impose de contrôler votre identité.'
          end
        end

        find('#cashway-confirm-btn').click
      end

      it "checks what to do next" do
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

      it "fetches order info" do
        $barcode = find('#cashway-barcode-label').text.tr(' ', '')
        $order_id = find('#shop-order-id').text
        $total = find('#payment')['data-payment']
      end

      it "posts payment notification to shop" do
        # FIXME: how do we get the shared_secret here?
        # In test, this should be randomly set on web server setup, as a global env var.
        shared_secret = 'howdy!'
        notify_url = ENV['TEST_SERVER'] + '/index.php?fc=module&module=cashway&controller=notification'
        pung = "php ../php/notify.php \"#{notify_url}\" transaction_paid \"#{$barcode}\" \"#{$order_id}\" \"#{$total}\" \"#{shared_secret}\""
        puts pung
        puts system(pung)
      end

      it "checks that the order _is_ paid on the shop" do
        # TODO: inspect order status as a customer
        # TODO: inspect order status as an admin
      end

    end

  end
end
