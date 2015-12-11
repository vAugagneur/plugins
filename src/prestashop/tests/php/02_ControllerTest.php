<?php
require_once __DIR__.'/../../lib/cashway/compat.php';
require_once __DIR__.'/../../lib/cashway/cashway_lib.php';

class CashWayControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerNotificationsSignatures
    */
    public function testNotificationSignature($url, $payload, $headers, $code, $status, $message, $agent)
    {
        $res = \CashWay\cURL::POST($url, json_encode($payload), null, $headers, 'TEST-UA');
        $this->assertEquals(array(
             'status' => $status,
            'message' => $message,
              'agent' => $agent
        ), json_decode($res['body'], true));
        $this->assertEquals($code, $res['code']);
    }

    public function providerNotificationsSignatures()
    {
        $url = sprintf('http://%s:%d/notification.php', $_SERVER['TEST_SERVER_HOST'], $_SERVER['TEST_SERVER_PORT']);

        return array(
            array($url, array(), array(
                ), 400, 'error', 'A signature header is required.',
                'CashWayModule/0.0.0 PrestaShop/1.1.1 PHP/'.PHP_VERSION.' '.PHP_OS
            ),
            array($url, array(), array(
                    'X-Cashway-Event: status_check',
                    'X-CashWay-Signature: unknown=bidon'
                ), 403, 'error', 'Unsupported signature algorithm.',
                'CashWayModule/0.0.0 PrestaShop/1.1.1 PHP/'.PHP_VERSION.' '.PHP_OS
            ),
            array($url, array(), array(
                    'X-Cashway-Event: status_check',
                    'X-CashWay-Signature: sha256=bidon'
                ), 403, 'error', 'Payload signature does not match.',
                'CashWayModule/0.0.0 PrestaShop/1.1.1 PHP/'.PHP_VERSION.' '.PHP_OS
            ),
            array($url, array(), array(
                    'X-Cashway-Event: status_check',
                    'X-CashWay-Signature: sha256=bidon'
                ), 403, 'error', 'Payload signature does not match.',
                'CashWayModule/0.0.0 PrestaShop/1.1.1 PHP/'.PHP_VERSION.' '.PHP_OS
            ),
            //5
            array($url, array('key' => 'value'),
                array(
                    'X-Cashway-Event: status_check',
                    'X-CashWay-Signature: sha256=4777d4fcfb3cf1db660f88162ac35571e60baf1309d70666675604aad4df99c2'
                ), 403, 'error', 'Payload signature does not match.',
                'CashWayModule/0.0.0 PrestaShop/1.1.1 PHP/'.PHP_VERSION.' '.PHP_OS
            ),
            array($url, array('key' => 'value'),
                array(
                    'X-Cashway-Event: status_check',
                    'X-CashWay-Signature: sha256=4777d4fcfb3cf1db660f88162ac35571e60baf1309d70666675604aad4df99c1'
                ), 200, 'ok', '[LOG] Test.',
                'CashWayModule/0.0.0 PrestaShop/1.1.1 PHP/'.PHP_VERSION.' '.PHP_OS
            )
        );
    }

    /**
     * @dataProvider providerNotifications
    */
    public function testNotification($url, $payload, $headers, $code, $status, $message, $agent)
    {
        $res = \CashWay\cURL::POST($url, json_encode($payload), null, $headers, 'TEST-UA');
        $this->assertEquals(array(
             'status' => $status,
            'message' => $message,
              'agent' => $agent
        ), json_decode($res['body'], true));
        $this->assertEquals($code, $res['code']);
    }

    public function buildNotification($params)
    {
        $body = json_encode($params);
        $headers = [
            'X-CashWay-Event: '.$params['event'],
            'X-CashWay-Signature: sha256='.\CashWay\API::signData(json_encode($params), 'sha256', $_SERVER['TEST_SHARED_SECRET'])
        ];
        return [
            $params,
            $headers
        ];
    }

    public function providerNotifications()
    {
        $url = sprintf('http://%s:%d/notification.php', $_SERVER['TEST_SERVER_HOST'], $_SERVER['TEST_SERVER_PORT']);

        return [
            array_merge(
                [$url],
                self::buildNotification([
                    'event' => 'transaction_expired',
                    'order_id' => 'TEST-ORDER-ID',
                    'barcode' => 'TEST-BARCODE',
                    'status' => 'expired',
                    'created_at' => 'C',
                    'paid_at' => null,
                    'expires_at' => 'C'
                ]),
                [200, 'ok', '[LOG] Test.', 'CashWayModule/0.0.0 PrestaShop/1.1.1 PHP/'.PHP_VERSION.' '.PHP_OS]
            ),
            array_merge(
                [$url],
                self::buildNotification([
                    'event' => 'status_check'
                ]),
                [200, 'ok', '[LOG] Test.', 'CashWayModule/0.0.0 PrestaShop/1.1.1 PHP/'.PHP_VERSION.' '.PHP_OS]
            )
        ];
    }

}
