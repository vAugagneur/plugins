<?php

date_default_timezone_set('Europe/Paris');

class ModuleFrontController
{
    public function __construct()
    {
    }
}

class Configuration
{
    public static function get($key)
    {
        $values = array(
            'PS_OS_ERROR' => 1,
            'CASHWAY_SHARED_SECRET' => 'howdy!',
            'CASHWAY_SEND_EMAIL' => false
        );

        if (array_key_exists($key, $values)) {
            return $values[$key];
        }

        return null;
    }
}

class Cashway
{
    const VERSION = '0.0.0';

    public static function checkForPayments()
    {
        echo "[LOG] Test.";
    }
}

define('_PS_VERSION_', '1.1.1');

require '../../../lib/cashway/cashway_lib.php';
require '../../../controllers/front/notification.php';

$cntr = new CashwayNotificationModuleFrontController();
$cntr->postProcess();
