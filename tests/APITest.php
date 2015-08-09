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
     * @dataProvider uaConfigProvider
    */
    public function testUserAgent($conf, $expected)
    {
        $a = new \CashWay\API($conf);
        $this->assertStringMatchesFormat($expected, $a->user_agent);
    }

    public function testSetOrderPrestaShop()
    {
        $a = new \CashWay\API(array());

        $res = $a->setOrder('prestashop', 1, new Cart(), new Customer(), 'FR', 'EUR');
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
        $this->assertJsonStringEqualsJsonString(json_encode($sent_data), $res['body']);
    }

    function testUpdateAccount()
    {
        $sent_data = array('key' => 'value');

        $api = new \CashWay\API(get_conf());
        $res = $api->updateAccount($sent_data);
        $this->assertJsonStringEqualsJsonString(json_encode($sent_data), $res['body']);
    }

    function testOpenTransaction()
    {
        $api = new \CashWay\API(get_conf());
        $sent_data = array(
            'agent'    => $api->user_agent,
            'order'    => $api->order,
            'customer' => $api->customer,
            'confirm'  => true
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
        function make_ruby_command($body, $secret)
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
            $ruby = str_replace('#3', 'sha512', $ruby);

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
            array('body1', 'secret1'),
            array(json_encode(array('key' => 'value', 'key2' => 2)), 'howdy!'),
            array(bin2hex(openssl_random_pseudo_bytes(128)), bin2hex(openssl_random_pseudo_bytes(32)))
        );

        foreach ($test_values as $k => $run)
        {
            $body = $run[0];
            $secret = $run[1];
            $output = array();
            $cmd = make_ruby_command($body, $secret);
            exec($cmd, $output);
            $vars = extract_vars($output);

            $test_values[$k][2] = $vars['signature'];
        }

        return $test_values;
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
        $this->date_add = date('c');
        $this->id_address_delivery = 'address-1';
    }

    public function nbProducts()
    {
        return 1;
    }

    public function getProducts()
    {
        return array(array(
            'name' => '',
            'cart_quantity' => 1,
            'price' => '10.00',
            'total' => '10.00',
            'rate' => 0
        ));
    }

    public function getOrderTotal($with_taxes = true, $type = self::BOTH)
    {

    }
}

class AddressCore {
    public function __construct()
    {
        $this->id = 'address-1';
        $this->phone = 'phone-1';
        $this->phone_mobile = 'phone-mobile-1';
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
        $this->date_add = date('c');
        $this->geoloc_id_country = 'FR';
        $this->geoloc_id_state = '44';
        $this->geoloc_postcode = '44100';

    }
}
