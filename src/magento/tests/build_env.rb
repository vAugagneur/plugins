require 'securerandom'
require 'rest-client'
require 'json'
require 'awesome_print'

API_SERVER='https://api-staging.cashway.fr/1/shops'

account = {
  firstname: 'Anne',
  lastname: 'de Bretagne ' + 6.times.map { [*'a'..'z'].sample }.join,
  email: 'test-%s@do.cshw.pl' % [SecureRandom.hex(8)],
  passwd: SecureRandom.hex(8),
  url: 'http://localhost:8080/prestashop/'
}

def register_shop_account(acc)
  begin
    res = RestClient.post API_SERVER,
      {
        email: acc[:email],
        name: acc[:firstname] + ' ' + acc[:lastname],
        password: SecureRandom.hex(10)
      }.to_json,
      accept: :json,
      content_type: :json

    data = JSON.parse(res.to_str)
    {
      api_key: data['api_key'],
      api_secret: data['api_secret']
    }
  rescue Exception => e
    ap e.response
    puts "Failed to register Shop account on #{API_SERVER}, abort ABORT."
    exit 1
  end
end

account.merge!(register_shop_account(account))

conf = <<CONF
TEST_SERVER=http://localhost:8080/magento/

CUSTOMER_NAME="${CUSTOMER_FIRSTNAME} ${CUSTOMER_LASTNAME}"
CUSTOMER_EMAIL=#{account[:email]}
CUSTOMER_PASSWD=#{account[:passwd]}
CUSTOMER_FIRSTNAME=#{account[:firstname]}
CUSTOMER_LASTNAME=#{account[:lastname]}
CUSTOMER_ADDRESS=Château
CUSTOMER_CITY=Nantes
CUSTOMER_ZIP=44000
CUSTOMER_PHONE=+33240000000
CUSTOMER_COUNTRY=FR

ADMIN_PATH=index.php/admin/
ADMIN_MANAGER_PATH=downloader/
ADMIN_FIRSTNAME=Admin
ADMIN_LASTNAME=Magento
ADMIN_USERNAME=magento
ADMIN_EMAIL=test-dff074b1a6azeazec04435@do.cshw.pl

# at last 8 digits, alphanum...
ADMIN_PASSWD=Magento0

MODULE_NAME=CashWay
MODULE_VERSION=0.2.0
MODULE_CHANNEL=community
MODULE_ARCHIVE=../build/${MODULE_NAME}-${MODULE_VERSION}.tgz
MODULE_PAY_ACTION_TEST=

API_KEY=
API_SECRET=
API_SHARED_SECRET=

CONF

puts conf
