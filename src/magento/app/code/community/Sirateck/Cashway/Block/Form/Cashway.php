<?php
/**
 *  CASHWAY
 *
 *  Copyright 2015 CashWay
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 * @category   Sirateck
 * @package    Sirateck_Cashway
 * @copyright  Copyright 2015 CashWay
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License, Version 2.0
 */

/**
 * Block for select payment method FORM
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 */
class Sirateck_Cashway_Block_Form_Cashway extends Mage_Payment_Block_Form
{
    protected $_evaluateTransaction = null;

    protected $_methodInstance = null;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('cashway/form/cashway.phtml');
    }

    /**
     * @return $methodInstance Sirateck_Cashway_Model_Method_Cashway
     */
    protected function _getMethodinstance()
    {
        if (is_null($this->_methodInstancehod)) {
            $this->_methodInstance = $this->getMethod();
        }

        return $this->_methodInstance;
    }

    /**
     * Return result of evaluate transaction
     * @return Sirateck_Cashway_Model_Response_Order
     */
    public function getEvaluateTransaction()
    {
        if (is_null($this->_evaluateTransaction)) {
            $this->_evaluateTransaction = $this->_getMethodinstance()->evaluateTransaction($this->getQuote());
        }

        return $this->_evaluateTransaction;
    }

    public function isExcessiveAmount($amount)
    {
        return $amount <= 1000;
    }

    /**
     * Check if cashway service is available with evaluateTransaction api endpoint
     * @return boolean
     */
    public function canWork()
    {
    	try{
    		$this->getEvaluateTransaction();
    		return true;
    	}
    	catch (Exception $e){
    		$this->_getMethodinstance()->debugData($e->getMessage());
    	}

    	return false;

    }

    /**
     * Retrieve formated address one line
     * @return string
     */
    public function getAddressOneLine()
    {
        $addrParts = explode(",", $this->getQuote()->getBillingAddress()->format('oneline'));
        array_shift($addrParts);
        return implode(",", $addrParts);
    }

    /**
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }
}
