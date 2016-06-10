<?php

class FeeTest extends PHPUnit_Framework_TestCase
{
    public function testFees()
    {
        #TODO The webserver can't manage this type of requests
        # For now, it would be interesting to make it do so
        /*$api = new \CashWay\API(get_conf());
        $values = array(
            array(0, 0.0),
            array(9, 1.0),
            array(49, 1.0),
            array(50, 1.0),
            array(51, 2.0),
            array(150, 2.0),
            array(151, 3.0),
            array(250, 3.0),
            array(250.50, 4.0)
        );
        foreach ($values as $value) {
            $this->assertEquals($value[1], $api->getCustomerFees($value[0]));
        }*/
    }
}
