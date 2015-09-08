<?php

class Sirateck_Cashway_Block_Info_Cashway extends Mage_Payment_Block_Info
{


    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('cashway/info/cashway.phtml');
    }

}
