<?php
/**
 * Used to test transaction payment notification against a real test order on a Shop.
 * See src/prestashop/tests/spec/client_use.rb
*/

require __DIR__.'/../../../php/cashway_lib.php';

function usage()
{
    echo "Usage: php notify.php URL EVENT BARCODE ORDER_ID PAID ORDER_TOTAL SECRET\n";
}

/* Taken from 02_ControllerTest.php and adjusted */
function buildNotification($params, $secret)
{
    $body = json_encode($params);
    $headers = [
        'X-CashWay-Event: '.$params['event'],
        'X-CashWay-Signature: sha256='.\CashWay\API::signData(json_encode($params), 'sha256', $secret)
    ];
    return [
        $params,
        $headers
    ];
}

if (count($argv) != 8) {
    usage();
    exit(1);
}

list ($script, $url, $event, $barcode, $order_id, $paid, $total, $secret) = $argv;

$data = buildNotification([
         'event' => $event,
      'order_id' => $order_id,
       'barcode' => $barcode,
        'status' => str_replace('transaction_', '', $event),
   'order_total' => $total,
   'paid_amount' => $paid,
    'created_at' => date('c'),
       'paid_at' => date('c'),
    'expires_at' => date('c')
], $secret);

print_r($data);
$res = \CashWay\cURL::POST($url, json_encode($data[0]), null, $data[1], 'TEST-UA');

echo 'Code:  ', $res['code'], "\n";
echo 'Body:  ';
print_r(json_decode($res['body']));
echo 'Error: ', $res['error'], "\n";



