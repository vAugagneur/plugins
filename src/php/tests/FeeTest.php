<?php

class FeeTest extends PHPUnit_Framework_TestCase
{
    public function testFees()
    {
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
            $this->assertEquals($value[1], \CashWay\Fee::getCartFee($value[0]));
        }
    }
}