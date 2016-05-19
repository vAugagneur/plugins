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
 * Instant payment notification controller
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 */
class Sirateck_Cashway_IpnController extends Mage_Core_Controller_Front_Action
{
    /**
     *
     * @var Mage_Sales_Model_Order $_order
     */
    protected $_order = null;

    /**
     *
     * @var array
     */
    protected $_data = null;

    /**
     * @return Mage_Core_Controller_Front_Action
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->_validateSignature()) { //check for signature
            //If signature is wrong we set flag to no-dispatch and output erroe message
            $this->getResponse()->setBody("NOK. Wrong Signature!");
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    /**
     * Check signature from Cashway servers
     * Value to check from cashway is in SERVER VAR "HTTP_X_CASHWAY_SIGNATURE"
     * SharedSecret of magento is stored in configuration
     */
    protected function _validateSignature()
    {
        if ($this->getData()) {//Check if datas are presents
        //We load order to get the storeId of this order and get the good shared key
            // FIXME: signature must be checked independently of payload contents (here order id)
            /* @var $order Mage_Sales_Model_Order */
            $order = Mage::getModel('sales/order')->loadByIncrementId($this->getData()->order_id);

            if ($order->getId()) {
                $this->_order = $order;
                $method = $order->getPayment()->getMethodInstance();
                $sharedSecret = $this->getConfig()->getApiSharedSecret($order->getStoreId());
                if ($method->getConfigData('is_test_mode')) {
                    $sharedSecret = $this->getConfig()->getApiSharedSecretTest($order->getStoreId());
                }

                $signature = explode("=", $this->getRequest()->getServer('HTTP_X_CASHWAY_SIGNATURE'));

                return hash_hmac($signature[0], $this->getRequest()->getRawBody(), $sharedSecret, false) === $signature[1];
            }
        }

        return false;
    }

    /**
     * Get the data from Raw body and apply json_decode
     * @return array
     */
    public function getData()
    {
        if (is_null($this->_data)) {
            $this->_data = json_decode($this->getRequest()->getRawBody());
        }

        return $this->_data;
    }

    /**
     *
     * @return Mage_Sales_Model_Order
     */
    protected function getOrder()
    {
        return $this->_order;
    }

    /**
     * Entry for IPN
     * When an event occure in cashway workflow, the information is dispatched here
     * Url: https://www.mystore.com/cashway/ipn/index/
     */
    public function indexAction()
    {
        //We revover the value of event server variable
        $event = $this->getRequest()->getServer('HTTP_X_CASHWAY_EVENT');

        //If conversion is expired
        if ($event == 'conversion_expired') {
        //We cancel the order
            if ($this->getOrder()->canCancel()) {
                $this->getOrder()->cancel();
            }

            //Add comment to order
            $comment = Mage::helper('cashway')->__("Conversion Expired at %s.", $this->getData()->expires_at);
            $this->getOrder()->addStatusToHistory(true, $comment);

            //save of order
            $this->getOrder()->save();

            //Return success response: OK
            $this->getResponse()->setBody("OK");

            return $this;
        }

        //Get resource transaction objet
        $res = Mage::getModel('core/resource_transaction');
        //Add order to resource object for prepare save
        $res->addObject($this->getOrder());

        switch ($event) {
        //If event is transaction confirmed
            case "confirmed":
                //we just add a commentto order
                $comment = Mage::helper('cashway')->__("Transaction confimed with status %s.", $this->getData()->status);
                $this->getOrder()->addStatusToHistory(true, $comment);

                break;
            case "paid":
                    // FIXME: if already paid, should reply with a status code and not update database.
                    //We change state of the order to processing
                    $this->getOrder()->setState(
                        Mage_Sales_Model_Order::STATE_PROCESSING,
                        true,
                        Mage::helper('cashway')->__("Transaction paid at %s.", $this->getData()->paid_at),
                        $notified = true
                    );

                    //prepare invoice object
                    $invoice = $this->getOrder()->prepareInvoice();
                    $invoice->register(); //register it (set all totals)
                    $invoice->getOrder()->setIsInProcess(true);
                    $invoice->setIsPaid(1);//set is paid

                    if (!$this->getOrder()->getEmailSent()) { // If email was not send before
                        $this->getOrder()->sendNewOrderEmail();//We send it
                    }

                    $res->addObject($invoice); //Add invoir to resource objecy

                break;
            case "expired": //Transaction expired when wasn't paid in delay
                //We cancel the order
                $this->getOrder()->cancel();
                //And add a comment
                $comment = Mage::helper('cashway')->__("Transaction Expired at %s.", $this->getData()->expires_at);
                $this->getOrder()->addStatusToHistory(true, $comment);
                break;
            case "cancelled": //Transaction was canceled for any reason
                //We cancel the order
                $this->getOrder()->cancel();
                //And add a comment
                $comment = Mage::helper('cashway')->__("Transaction Canceled with status %s.", $this->getData()->status);
                $this->getOrder()->addStatusToHistory(true, $comment);
                break;
            case "blocked": //Transaction was blocked for any reason
                //We hold the order
                if ($this->getOrder()->canHold()) {
                    $this->getOrder()->hold();
                }

                //And add a comment
                $comment = Mage::helper('cashway')->__("Transaction Blocked with status %s.", $this->getData()->status);
                $this->getOrder()->addStatusToHistory(true, $comment);
                break;
            default:
                //by default we add comment with data received
                $comment = Mage::helper('cashway')->__("Notification received with data %s.", print_r($this->getData(), true));
                $this->getOrder()->addStatusToHistory(true, $comment);
                break;
        }
        //Save objects in resource transaction
        $res->save();

        //Return OK resposne
        $this->getResponse()->setBody("OK");
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
