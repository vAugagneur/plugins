<?php

require_once 'test_lib.php';
require_once __DIR__.'/../../cashway.php';

class CashWayModuleTest extends PHPUnit_Framework_TestCase
{
    public function expectedAuthHeader()
    {
        return 'Basic '.base64_encode($_SERVER['TEST_KEY'].':'.$_SERVER['TEST_SECRET']);
    }

    public function testHookActionOrderStatusUpdate()
    {
        $cwmod = new CashWay();
        $os = new stdClass();
        $os->id = Configuration::get('PS_OS_ERROR');
        $params = array(
            'newOrderStatus' => $os,
            'id_order' => 1
        );

        $res = $cwmod->hookActionOrderStatusUpdate($params);
        $this->assertEquals('POST', $res['method']);
        $this->assertEquals($this->expectedAuthHeader(), $res['headers']['Authorization']);
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
        $this->assertEquals($this->expectedAuthHeader(), $res['headers']['Authorization']);
        $this->assertEquals('/1/shops/me', $res['request']);

        $body = json_decode($res['body']);
        $this->assertEquals('scheme://host.tld/cashway/notification', $body->notification_url);
    }
}
