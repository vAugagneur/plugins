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
 * Observer class
 * @author Kassim Belghait <kassim@sirateck.com>
 */
class Sirateck_Cashway_Model_Observer
{
    /**
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Validate data for redirect to reorder cashway
     * @param $lastPaymentMethodInstance Mage_Payment_Model_Method_Abstract
     */
    protected function _validateRedirect($lastPaymentMethodInstance)
    {

        if (!$this->getCashway()->getConfigData('active') ||
                !$this->getCashway()->getConfigData('allowredirect') ||
                $lastPaymentMethodInstance->getCode() == 'cashway') {
            return false;
        }

        //check congifuration for filter by payment method
        $isEnabledForSpecificMethod = $this->getCashway()->getConfigData('redirectspecific');

        if ($isEnabledForSpecificMethod) {
            $methodsAllowed = explode(",", $this->getCashway()->getConfigData('redirectmethod'));

            if (!in_array($lastPaymentMethodInstance->getCode(), $methodsAllowed)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return Sirateck_Cashway_Model_Method_Cashway
     */
    protected function getCashway()
    {
        return Mage::getSingleton('cashway/method_cashway');
    }

    /**
     *
     * @param Mage_Sales_Model_Quote $lastQuote
     */
    protected function _reactiveLastQuote($lastQuote, $methodToSet = 'cashway')
    {
        $data = array('method'=>$methodToSet);

        $lastQuote->getBillingAddress();
        $lastQuote->getShippingAddress()->setCollectShippingRates(true);

        //reactive the quote
        $lastQuote->setIsActive(true);

        //Import data cashway payment (collectTotals is called in importData method)
        //Cashway availability in this quote is check in importData
        $lastQuote->getPayment()->importData($data);

        $lastQuote->save();

        $this->_getCheckout()->setQuoteId($lastQuote->getId());
    }

    /**
     * When checkout action redirect to failure page, we redirect to cashway page
     * If it's check configuration
     *
     * @param Varien_Object $observer
     * @return Sirateck_Cashway_Model_Observer
     */
    public function redirectToCashway($observer)
    {
        $lastQuoteId = $this->_getCheckout()->getLastQuoteId();
        $lastOrderId = $this->_getCheckout()->getLastOrderId();

        if (!$lastQuoteId || !$lastOrderId) {
            return false ;
        }

        //Reload the Quote
        /* @var $lastQuote Mage_Sales_Model_Quote */
        $lastQuote = Mage::getModel('sales/quote')->load($lastQuoteId);
        $lastOrder = MAge::getModel('sales/order')->load($lastOrderId);

        $lastPaymentMethodInstance = $lastQuote->getPayment()->getMethodInstance();

        $cashwayInstance = Mage::getSingleton('cashway/method_cashway');

        if (!$this->_validateRedirect($lastPaymentMethodInstance)) {
            return $this;
        }

        $this->_reactiveLastQuote($lastQuote);

        $this->_getCheckout()->setLastPaymentMethodTitle($lastPaymentMethodInstance->getConfigData('title'));

        $this->getCashway()->sendEventPaymentFailed($lastOrder);

        /* @var $ex Mage_Core_Controller_Varien_Exception */
        $ex = new Mage_Core_Controller_Varien_Exception();

        /**
         * Cannot use prepareRedirect method because it contain a bug
         * So we found a hack solution
         * File: app/code/core/Mage/Core/Controller/Varien/Exception.php
         * Line: 70
         * Magento version: 1.9.1.0
         * //$ex->prepareRedirect("cashway/reorder/",array());//SET Path to redirect and arguments
         */

        //Hack we pass path instead actionName and array instead controllerName
        $ex->prepareForward("cashway/reorder/", array());

        //Hack we pass _resultCallback on method prepareFork instead actionName
        //It's override _resultCallback from prepareForward method
        $ex->prepareFork(Mage_Core_Controller_Varien_Exception::RESULT_REDIRECT);

        //Throw Exception will be catched by Mage_Core_Controller_Varien_Action
        throw $ex;
    }

    /**
     * When configuration is saved, it's dispatch event "core_config_data_save_after"
     * And we need to update new shared_secret to cashway account
     *
     * @param Varien_Object $observer
     * @return Sirateck_Cashway_Model_Observer
     */
    public function updateAccount($observer)
    {
        /* @var $configData Mage_Core_Model_Config_Data */
        $configData = $observer->getConfigData();

        if ($configData->getGroupId() == 'cashway_api') {//Check for configuration group
            $value = (string)$configData->getValue();// Get crypted value
            //Decrypt it, if is not empty
            if (!empty($value) && ($decrypted = Mage::helper('core')->decrypt($value))) {
                $value = $decrypted;
            }

            if ($configData->getField() == 'api_shared_secret') {
                if ($this->getConfig()->getApiKey() != "" && $this->getConfig()->getApiSecret() != "") {
                    $params = array();
                    $params['notification_url'] = Mage::getSingleton('cashway/config')->getIpnUrl();
                    $params['shared_secret'] = $value;

                    /* @var $request Sirateck_Cashway_Model_Api_Request */
                    $request = Mage::getModel('cashway/api_request');
                    $request->setData('api_key', $this->getConfig()->getApiKey());
                    $request->setData('api_secret', $this->getConfig()->getApiSecret());

                    //Send new datas to cashway
                    $accountResponse = $request->updateOrGetAccountRequest(Sirateck_Cashway_Model_Api_Request::ACTION_UPDATE_ACCOUNT, $params);

                }

            } elseif ($configData->getField() == 'api_shared_secret_test') {
                if ($this->getConfig()->getApiKeyTest() != "" && $this->getConfig()->getApiSecretTest() != "") {
                    $params = array();
                    $params['notification_url'] = Mage::getSingleton('cashway/config')->getIpnUrl();
                    $params['shared_secret'] = $value;

                    /* @var $request Sirateck_Cashway_Model_Api_Request */
                    $request = Mage::getModel('cashway/api_request');
                    $request->setData('api_key', $this->getConfig()->getApiKeyTest());
                    $request->setData('api_secret', $this->getConfig()->getApiSecretTest());

                    $accountResponse = $request->updateOrGetAccountRequest(Sirateck_Cashway_Model_Api_Request::ACTION_UPDATE_ACCOUNT, $params);

                }

            }
        }

        return $this;
    }

    /**
     *
     * @return Sirateck_Cashway_Model_Config $config
     */
    protected function getConfig()
    {
        return Mage::getSingleton('cashway/config');
    }
}
