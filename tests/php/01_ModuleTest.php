<?php

require_once 'test_lib.php';
require_once __DIR__.'/../../cashway.php';

class CashWayModuleTest extends PHPUnit_Framework_TestCase
{
    public function testHookActionOrderStatusUpdate()
    {
        $cwmod = new CashWay();
        $os = new stdClass();
        $os->id = 1;
        $params = array(
            'newOrderStatus' => $os,
            'id_order' => 1
        );

        $res = $cwmod->hookActionOrderStatusUpdate($params);
        var_dump($res);
        $this->assertEquals('POST', $res['method']);
        $this->assertEquals('Basic '.base64_encode(TEST_KEY.':'.TEST_SECRET), $res['headers']['Authorization']);
        $this->assertEquals('/1/shops/me/events', $res['request']);

        $body = json_decode($res['body']);
        $this->assertEquals('payment_failed', $body->event);
        $this->assertEquals('test_payment_method', $body->provider);
        $this->assertEquals(1, $body->order->id);
        $this->assertEquals('test.customer@do.cshw.pl', $body->customer->email);
    }

    public function testUpdateNotificationParameters()
    {
        $cwmod = new CashWay();
        $res = $cwmod->updateNotificationParameters();
        $this->assertEquals('POST', $res['method']);
        $this->assertEquals('Basic '.base64_encode(TEST_KEY.':'.TEST_SECRET), $res['headers']['Authorization']);
        $this->assertEquals('/1/shops/me', $res['request']);

        $body = json_decode($res['body']);
        $this->assertEquals('scheme://host.tld/cashway/notification', $body->notification_url);
    }
}
