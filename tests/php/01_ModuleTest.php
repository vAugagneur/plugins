<?php

require_once 'test_lib.php';
require_once __DIR__.'/../../cashway.php';

class CashWayModuleTest extends PHPUnit_Framework_TestCase
{
    public function testHookActionOrderStatusUpdate()
    {
        $cwmod = new Cashway();
        $os = new stdClass();
        $os->id = 1;
        $params = array(
            'newOrderStatus' => $os,
            'id_order' => 1
        );
        $res = $cwmod->hookActionOrderStatusUpdate($params);
        $this->assertEquals($res['headers']['Authorization'], 'Basic '.base64_encode(TEST_KEY.':'.TEST_SECRET));
        $this->assertEquals($res['method'], 'POST');
        $this->assertEquals($res['request'], '/1/shops/me/events');

        $body = json_decode($res['body'], true);
        $this->assertEquals($body['event'], 'payment_failed');
        $this->assertEquals($body['provider'], 'test_payment_method');
        $this->assertEquals($body['order']['id'], 1);
        $this->assertEquals($body['customer']['email'], 'test.customer@do.cshw.pl');
    }
}
