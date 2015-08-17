<?php

require __DIR__.'/../../lib/cashway/compat.php';
require __DIR__.'/../../lib/cashway/cashway_lib.php';

class ModuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerTest
    */
    public function testThis($url, $payload, $headers, $code, $status, $message)
    {
        $res = \CashWay\cURL::POST($url, json_encode($payload), null, $headers, 'TEST-UA');
        $this->assertEquals(array(
            'status' => $status,
            'message' => $message
        ), json_decode($res['body'], true));
        $this->assertEquals($code, $res['code']);
    }

    public function providerTest()
    {
        $url = sprintf('http://%s:%d/notification.php', WEB_SERVER_HOST, WEB_SERVER_PORT);

        return array(
            array($url, array(), array(), 400, 'error', 'A signature header is required.'),
            array($url, array(), array(
                    'X-Cashway-Event: status_check',
                    'X-CashWay-Signature: unknown=bidon'
                ), 400, 'error', 'Unsupported signature algorithm.'
            ),
            array($url, array(), array(
                    'X-Cashway-Event: status_check',
                    'X-CashWay-Signature: sha256=bidon'
                ), 400, 'error', 'Payload signature does not match.'
            ),
            array($url, array(), array(
                    'X-Cashway-Event: status_check',
                    'X-CashWay-Signature: sha256=bidon'
                ), 400, 'error', 'Payload signature does not match.'
            ),
            //5
            array($url, array('key' => 'value'),
                array(
                    'X-Cashway-Event: status_check',
                    'X-CashWay-Signature: sha256=4777d4fcfb3cf1db660f88162ac35571e60baf1309d70666675604aad4df99c2'
                ), 400, 'error', 'Payload signature does not match.'
            ),
            array($url, array('key' => 'value'),
                array(
                    'X-Cashway-Event: status_check',
                    'X-CashWay-Signature: sha256=4777d4fcfb3cf1db660f88162ac35571e60baf1309d70666675604aad4df99c1'
                ), 200, 'ok', array(
                    'fn' => 'checkForPayments',
                    'log' => array('[LOG] Test.'),
                    'agent' => 'CashWayModule/0.0.0 PrestaShop/1.1.1 PHP/5.5.22 Darwin'
                )
            )
        );
    }

    public function testHookActionOrderStatusUpdate()
    {
        $this->assertTrue(true);
        /** TODO */
    }
}
