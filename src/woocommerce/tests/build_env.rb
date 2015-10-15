
require 'securerandom'
require 'rest-client'
require 'json'
require 'awesome_print'

API_SERVER='https://api-staging.cashway.fr/1/shops'

account = {
  firstname: 'Anne',
  lastname: 'de Bretagne (' + SecureRandom.hex(3) + ')',
  email: 'test-%s@do.cshw.pl' % [SecureRandom.hex(8)],
  passwd: SecureRandom.hex(32),
  url: 'http://localhost:8080/wordpress/'
}

def register_shop_account(acc)
  begin
    res = RestClient.post API_SERVER,
      {
        email: acc[:email],
        name: acc[:firstname] + ' ' + acc[:lastname],
        password: SecureRandom.hex(32)
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
TEST_SERVER=#{account[:url]}
SERVER_COUNTRY=France

CUSTOMER_FIRSTNAME=#{account[:firstname]}
CUSTOMER_LASTNAME=#{account[:lastname]}
CUSTOMER_NAME="${CUSTOMER_FIRSTNAME} ${CUSTOMER_LASTNAME}"
CUSTOMER_EMAIL=#{account[:email]}
CUSTOMER_PASSWD=#{account[:passwd]}
CUSTOMER_ZIPCODE=44000
CUSTOMER_PHONE="+33 4 00 00 00 00"
CUSTOMER_CITY=Nantes
CUSTOMER_ADRESS=Château

ADMIN_PATH=/admin
ADMIN_NAME=${CUSTOMER_NAME}
ADMIN_EMAIL=${CUSTOMER_EMAIL}
ADMIN_PASSWD=${CUSTOMER_PASSWD}

MODULE_NAME=CashWay
MODULE_ARCHIVE=../releases/file.zip
MODULE_PAY_ACTION_TEST=

API_KEY=#{account[:api_key]}
API_SECRET=#{account[:api_secret]}
CONF

puts conf

