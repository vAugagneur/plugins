<?php

class APITest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider urlConfigProvider
    */
    public function testApiUrlDefinition($conf, $expected)
    {
        $a = new \CashWay\API($conf);
        $this->assertEquals($expected, $a->api_base_url);
    }

    /**
     * @dataProvider signaturesProvider
    */
    public function testNotificationSignature($body, $secret, $expected_signature)
    {
        $this->assertTrue(\CashWay\API::isDataValid($body, $secret, $expected_signature));
    }

    /**
     * @dataProvider notificationsProvider
    */
    public function testReceiveNotification($body, $headers, $secret, $expected)
    {
        $this->assertEquals($expected, \CashWay\API::receiveNotification($body, $headers, $secret));
    }

    /**
     * @dataProvider uaConfigProvider
    */
    public function testUserAgent($conf, $expected)
    {
        $a = new \CashWay\API($conf);
        $this->assertStringMatchesFormat($expected, $a->user_agent);
    }

    public function testSetOrderPrestaShop()
    {
        $api = new \CashWay\API(get_conf());
        $api->setOrder('prestashop', 1, new Cart(), new Customer(), 'FR', 'EUR');
        $res = $api->openTransaction();

        $this->assertEquals('POST', $res['method']);
        $this->assertEquals('/1/transactions/', $res['request']);

        $expected_data = array(
            'agent' => 'CashWay/' . (string)\CashWay\VERSION . ' PHP/' . (string)PHP_VERSION . ' Darwin',
            'order' => array(
                'id' => 1,
                'at' => '2015-01-02T03:04:06Z',
                'currency' => 'EUR',
                'total' => '10.00',
                'language' => 'FR',
                'items_count' => 1,
                'details' => array(array(
                    'name' => 'product-1',
                    'price' => 1.00,
                    'quantity' => 1
                ))
            ),
            'customer' => array(
                'id' => 'customer-1',
                'name' => 'Adam Homme',
                'email' => 'adam@terre.eden',
                'phone' => array('phone-1', 'phone-mobile-1'),
                'city' => 'city-1',
                'zipcode' => 'postcode-1',
                'country' => 'country-1',
                'address' => array(
                    'invoice' => array(
                        'id' => 'address-1',
                        'address' => 'address-text',
                        'address2' => 'address-text2',
                        'phone' => 'phone-1',
                        'phone_mobile' => 'phone-mobile-1',
                        'city' => 'city-1',
                        'country' => 'country-1',
                        'postcode' => 'postcode-1'
                    ),
                    'delivery' => array(
                        'id' => 'address-2',
                        'address' => 'address-text',
                        'address2' => 'address-text2',
                        'phone' => 'phone-1',
                        'phone_mobile' => 'phone-mobile-1',
                        'city' => 'city-1',
                        'country' => 'country-1',
                        'postcode' => 'postcode-1'
                    ),
                ),
                'ip' => array(),
                'company' => 'Tout le monde',
                'siret' => '0',
                'ape' => '9001Z',
                'risk' => 'risk-1',
                'created_at' => '2015-01-02T03:04:05Z',
                'geoloc' => array(
                    'country' => 'FR',
                    'state' => '44',
                    'postcode' => '44100'
                )
            ),
            'more' => null
        );
        $this->assertJsonStringEqualsJsonString(json_encode($expected_data), $res['body']);
    }

    /**
     * @expectedException \DomainException
    */
    public function testSetOrderUnknownPlatform()
    {
        $a = new \CashWay\API(array());
        $a->setOrder('unknown');
    }

    public function testRegisterAccount()
    {
        $sent_data = array('key' => 'value');

        $api = new \CashWay\API(get_conf());
        $res = $api->registerAccount($sent_data);
        $this->assertEquals($sent_data, json_decode($res['body'], true));
    }

    function testUpdateAccount()
    {
        $sent_data = array('key' => 'value');

        $api = new \CashWay\API(get_conf());
        $res = $api->updateAccount($sent_data);
        $this->assertEquals($sent_data, json_decode($res['body'], true));
    }

    function testOpenTransaction()
    {
        $api = new \CashWay\API(get_conf());
        $sent_data = array(
            'agent'    => $api->user_agent,
            'order'    => $api->order,
            'customer' => $api->customer,
            'confirm'  => true,
            'more'     => array()
        );

        $res = $api->openTransaction(true);
        $this->assertEquals('POST', $res['method']);
        $this->assertEquals('/1/transactions/', $res['request']);
        $this->assertJsonStringEqualsJsonString(json_encode($sent_data), $res['body']);
    }

    public function testConfirmTransaction()
    {
        $api = new \CashWay\API(get_conf());
        $sent_data = array(
            'agent'    => $api->user_agent,
            'order_id'   => 'ord-id',
            'email'      => null,
            'phone'      => null
        );

        $res = $api->confirmTransaction('tx-id', 'ord-id', null, null);
        $this->assertEquals('POST', $res['method']);
        $this->assertEquals('/1/transactions/tx-id/confirmation', $res['request']);
        $this->assertJsonStringEqualsJsonString(json_encode($sent_data), $res['body']);
    }

    public function testReportFailedPayment()
    {
        $api = new \CashWay\API(get_conf());
        $res = $api->reportFailedPayment('ord-id', 10.01, 'cust-id', 'cust-email', 'pprov', 'reasons');
        $this->assertEquals('POST', $res['method']);
        $this->assertEquals('/1/shops/me/events', $res['request']);
        $this->assertJsonStringEqualsJsonString(json_encode(array(
                'event' => 'payment_failed',
                'provider' => 'pprov',
                'reason' => 'reasons',
                'customer' => array('id' => 'cust-id', 'email' => 'cust-email'),
                'order' => array('id' => 'ord-id', 'total' => 10.01),
                'created_at' => date('c')
            )),
            $res['body']);
    }

    public function testCheckTransactions()
    {
        $api = new \CashWay\API(get_conf());
        $res = $api->checkTransactionsForOrders(array());
        $this->assertEquals('GET', $res['method']);
        $this->assertEquals('/1/shops/me/transactions?', $res['request']);
        $this->assertJsonStringEqualsJsonString(json_encode(null),
            $res['body']);
    }

    public function urlConfigProvider()
    {
        return array(
            array(
                'conf' => array(),
                'url'  => 'https://api.cashway.fr/1'
            ),
            array(
                'conf' => array('USE_STAGING' => 'oui'),
                'url'  => 'https://api-staging.cashway.fr/1'
            ),
            array(
                'conf' => array('API_URL' => 'http://example.org'),
                'url'  => 'http://example.org/1'
            ),
            array(
                'conf' => array('USE_STAGING' => 'oui', 'API_URL' => 'http://example.org'),
                'url'  => 'http://example.org/1'
            )
        );
    }

    public function signaturesProvider()
    {
        function make_ruby_command($body, $secret, $algo)
        {
            $ruby = <<<'R'
BODY='#1' SECRET='#2' ALGO='#3' ruby -e "
require 'openssl'
body = ENV['BODY']
secret = ENV['SECRET']
digest = OpenSSL::Digest.new(ENV['ALGO'])
signature = ENV['ALGO'] + '=' + OpenSSL::HMAC.hexdigest(digest, secret, body)
puts 'body=' + body
puts 'secret=' + secret
puts 'signature=' + signature
"
R;
            $ruby = str_replace('#1', $body, $ruby);
            $ruby = str_replace('#2', $secret, $ruby);
            $ruby = str_replace('#3', $algo, $ruby);

            return $ruby;
        }

        function extract_vars($output)
        {
            $vars = array();

            foreach ($output as $line) {
                $line = explode('=', $line);
                $key = array_shift($line);
                $line = implode('=', $line);
                $vars[$key] = $line;
            }

            return $vars;
        }

        $test_values = array(
            array('body1', 'secret1', 'sha256'),
            array(json_encode(array('key' => 'value')), 'howdy!', 'sha256'),
            array(bin2hex(openssl_random_pseudo_bytes(128)), bin2hex(openssl_random_pseudo_bytes(32)), 'sha256'),
        );

        foreach ($test_values as $k => $run)
        {
            $body = $run[0];
            $secret = $run[1];
            $algo = $run[2];
            $output = array();
            $cmd = make_ruby_command($body, $secret, $algo);
            exec($cmd, $output);
            $vars = extract_vars($output);

            $test_values[$k][2] = $vars['signature'];
        }

        return $test_values;
    }

    function notificationsProvider()
    {
        return array(
            array(
                '{"key":"value"}',
                array(
                    "X-CashWay-Event" => "testing_code",
                    "X-CashWay-Signature" => "sha256=4777d4fcfb3cf1db660f88162ac35571e60baf1309d70666675604aad4df99c1"
                ),
                'howdy!',
                array(true, 'testing_code', json_decode('{"key":"value"}'))
            ),
            array(
                '{"key":"value"}',
                array(),
                'howdy!',
                array(false, 'A signature header is required.', 400)
            ),
            array(
                '{"key":"value"}',
                array(
                    "X-CashWay-Event" => "testing_code",
                    "X-CashWay-Signature" => "sha256=4777d4fcfb3cf1db660f88162ac355"
                ),
                'howdy!',
                array(false, 'Payload signature does not match.', 403)
            ),
            array(
                '{"key":"value"}',
                array(
                    "X-CashWay-Event" => "testing_code",
                    "X-CashWay-Signature" => "none="
                ),
                'howdy!',
                array(false, 'A real signature is required.', 403)
            ),
            array(
                '{"key":"value"}',
                array(
                    "X-CashWay-Event" => "testing_code",
                    "X-CashWay-Signature" => ""
                ),
                'howdy!',
                array(false, 'A real signature is required.', 403)
            ),
            array(
                '{"key":"value"}',
                array(
                    "X-CashWay-Event" => "testing_code",
                    "X-CashWay-Signature" => "any=test"
                ),
                'howdy!',
                array(false, 'Unsupported signature algorithm.', 403)
            )
        );
    }

    function uaConfigProvider()
    {
        return array(
            array(
                'conf' => array(),
                'ua'   => 'CashWay/%d.%d.%d PHP/' . PHP_VERSION . ' ' . PHP_OS
            ),
            array(
                'conf' => array('USER_AGENT' => 'Test/1'),
                'ua'   => 'CashWay/%d.%d.%d Test/1 PHP/' . PHP_VERSION . ' ' . PHP_OS
            )
        );
    }
}

class Cart {
    const BOTH = 1;

    public function __construct()
    {
        $this->date_add = '2015-01-02T03:04:06Z';
        $this->id_address_invoice  = 'address-1';
        $this->id_address_delivery = 'address-2';
    }

    public function getProducts()
    {
        return array(array(
            'name' => 'product-1',
            'price' => 1.00,
            'cart_quantity' => 1
        ));
    }

    public function nbProducts()
    {
        return 1;
    }

    public function getOrderTotal($with_taxes = true, $type = self::BOTH)
    {
        return '10.00';
    }
}

class AddressCore {
    public function __construct($id)
    {
        $this->id = $id;
        $this->address = 'address-text';
        $this->address2 = 'address-text2';
        $this->phone = 'phone-1';
        $this->phone_mobile = 'phone-mobile-1';
        $this->city = 'city-1';
        $this->country = 'country-1';
        $this->postcode = 'postcode-1';
    }

    public function getFields()
    {
        return get_object_vars($this);
    }
}

class Customer {
    public function __construct()
    {
        $this->id = 'customer-1';
        $this->firstname = 'Adam';
        $this->lastname = 'Homme';
        $this->email = 'adam@terre.eden';
        $this->company = 'Tout le monde';
        $this->siret = '0';
        $this->ape = '9001Z';
        $this->id_risk = 'risk-1';
        $this->date_add = '2015-01-02T03:04:05Z';
        $this->geoloc_id_country = 'FR';
        $this->geoloc_id_state = '44';
        $this->geoloc_postcode = '44100';

    }
}
