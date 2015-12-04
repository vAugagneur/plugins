<?php
/**
*/

date_default_timezone_set('Europe/Paris');

class ModuleFrontController
{
    public function __construct()
    {
    }
}

class Link
{
    public function getModuleLink($name, $type)
    {
        return 'scheme://host.tld/'.$name.'/'.$type;
    }
}

class PaymentModule
{
    public function __construct()
    {
        $this->context = new stdClass;
        $this->context->link = new Link;
    }

    public function l($s)
    {
        return $s;
    }
}

class Configuration
{
    public static function get($key)
    {
        $values = array(
            'PS_OS_ERROR' => 1,
            'CASHWAY_SHARED_SECRET' => 'howdy!',
            'CASHWAY_SEND_EMAIL' => false,
            'CASHWAY_USE_STAGING' => true,
            'CASHWAY_API_KEY' => 'test-key-K',
            'CASHWAY_API_SECRET' => 'test-secret-S'
        );

        if (array_key_exists($key, $values)) {
            return $values[$key];
        }

        return null;
    }
}

class Order
{
    public function __construct($id = null)
    {
        $this->id = $id;
        $this->id_customer = 1;
        $this->payment = 'test_payment_method';
    }

    public function addOrderPayment($order_total, $reason, $reference)
    {
        echo sprintf(
            "Adding payment of %.2f because of %s, %s for order %d\n",
            $order_total,
            $reason,
            $reference,
            $this->id
        );
    }

    public function setInvoice($send)
    {
        echo sprintf("Setting invoice for order %d\n", $this->id);
    }
}

class OrderHistory
{
    public function __construct()
    {
        $this->id_order = null;
    }

    public function changeIdOrderState($state, $order)
    {
        echo sprintf(
            "Calling changeIdOrderState(%s, %d) for order %d\n",
            $state,
            $order->id,
            $this->id_order
        );
    }

    public function addWithEmail($bool)
    {
        //
    }
}

class Customer
{
    public function __construct($id = null)
    {
        $this->id = $id;
        $this->email = 'test.customer@do.cshw.pl';
    }
}

class Tools
{
    public static function file_get_contents($file)
    {
        return file_get_contents($file);
    }
}

class Validate
{
    public static function isLoadedObject($obj)
    {
        return is_object($obj);
    }
}


define('_PS_VERSION_', '1.1.1');
