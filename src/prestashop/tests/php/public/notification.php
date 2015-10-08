<?php

require '../test_lib.php';
require '../../../lib/cashway/cashway_lib.php';
require '../../../controllers/front/notification.php';

class Cashway
{
    const VERSION = '0.0.0';

    public static function checkForPayments()
    {
        echo "[LOG] Test.";
    }
}

$cntr = new CashwayNotificationModuleFrontController();
$cntr->postProcess();
