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

    /**
     * @dataProvider ordersProvider
    */
    public function testReviewOrder($reason, $result, $ref, $remote, $local)
    {
        $this->assertEquals(
            $result,
            Cashway::reviewOrder($ref, $remote, $local),
            $reason
        );
    }

    /**
     * @dataProvider ordersListsProvider
    */
    public function testReviewKnownOrders($reason, $result, $local, $remote)
    {
        $this->assertEquals($result, Cashway::reviewKnownOrders($local, $remote), $reason);
    }

    public function ordersListsProvider()
    {
        return [
            [
                'reason' => 'Both lists are empty',
                'result' => [],
                'local' => [],
                'remote' => []
            ],
            [
                'reason' => 'Local list, no remote list',
                'result' => [],
                'local' => ['A' => [], 'B' => []],
                'remote' => []
            ],
            [
                'reason' => 'No local list, remote list',
                'result' => [],
                'local' => [],
                'remote' => ['A' => [], 'B' => []]
            ],
            [
                'reason' => 'Both list have common elements',
                'result' => ['B' => true],
                'local' => ['A' => [], 'B' => []],
                'remote' => ['B' => ['status' => 'open'], 'C' => []]
            ]
        ];
    }

    private static function buildOrder(
        $reason, $result,
        $id,
        $remote_status, $remote_expected, $remote_paid,
        $local_status, $local_expected, $local_paid
    )
    {
        return [
            'reason' => $reason,
            'result' => $result,
            'ref' => $id,
            'remote' => [
                'shop_order_id' => $id,
                'barcode' => 'BARCODE',
                'status' =>  $remote_status,
                'order_total' => $remote_expected,
                'paid_amount' => $remote_paid
            ],
            'local' => [
                'reference' => $id,
                'id_order' => 1,
                'current_state' => $local_status,
                'total_paid' => $local_expected,
                'total_paid_real' => $local_paid,
            ]
        ];
    }

    public function ordersProvider()
    {
        return [
            self::buildOrder('blocked, unpaid order should be dismissed', true,
                'OID-1', 'blocked',   10.0, 0.0, 0, 0.0, 0.0),

            self::buildOrder('confirmed, unpaid order should be dismissed', true,
                'OID-2', 'confirmed', 10.0, 0.0, 0, 0.0, 0.0),

            self::buildOrder('open, unpaid order should be dismissed', true,
                'OID-2b', 'open',     10.0, 0.0, 0, 0.0, 0.0),

            self::buildOrder('expired order should update local record', true,
                'OID-3', 'expired',   10.0, 0.0, 0, 10.0, 0.0),

            self::buildOrder('expected paid order should update status', true,
                'OID-4', 'paid', 10.0, 10.0, 0, 10.0,  0.0),

            self::buildOrder('paid order, already set should update status', true,
                'OID-5', 'paid', 10.0, 10.0, 1, 10.0, 10.0),

            self::buildOrder('paid order, less than expected, should be refused', false,
                'OID-6', 'paid', 10.0,  9.0, 0, 10.0,  0.0),

            self::buildOrder('paid order, more than expected, should be refused', false,
                'OID-7', 'paid', 10.0, 10.0, 0,  9.0,  0.0),

            self::buildOrder('paid order, unexpected, should be refused', false,
                'OID-8', 'paid', 10.0,  0.0, 0,  0.0,  0.0)
        ];
    }
}
