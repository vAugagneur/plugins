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
 * The Standard payment method
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 */
class Sirateck_Cashway_Model_Method_Cashway extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'cashway';
    protected $_formBlockType = 'cashway/form_cashway';
    protected $_infoBlockType = 'cashway/info_cashway';

    /**
     * Initialize is needed ?
     * @var boolean
     */
    protected $_isInitializeNeeded = true;

    public function isAvailable($quote = null)
    {
        return parent::isAvailable($quote);
    }

    /**
     *
     * In our case, we send order's datas to cashway for save and confirm order
     *
     * {@inheritDoc}
     * @see Mage_Payment_Model_Method_Abstract::initialize()
     */
    public function initialize($paymentAction, $stateObject)
    {
        /* @var $payment Mage_Sales_Model_Order_Payment */
        $payment = $this->getInfoInstance();

        $order = $payment->getOrder();


        $request = Mage::getModel('cashway/api_request', array($this));

        /* @var $request Sirateck_Cashway_Model_Api_Request */
        $orderResponse = $request->saveOrderRequest(Sirateck_Cashway_Model_Api_Request::ACTION_SAVE_ORDER, $this->getOrderParams($payment), $payment->getOrder()->getStoreId());
        $this->_debug($orderResponse->debug());

        //Check if barcode is present ans if status is open
        if ($orderResponse->getBarcode() != "" && $orderResponse->getStatus() == "open") {
        //In this case, we records received datas
            $payment->setAdditionalInformation('cashway_barcode', $orderResponse->getBarcode());
            $confirmResponse = $request->confirmOrderRequest(Sirateck_Cashway_Model_Api_Request::ACTION_CONFIRM_ORDER, $orderResponse->getBarcode(), $this->getConfirmOrderParams($payment), $payment->getOrder()->getStoreId());
            $this->_debug($confirmResponse->debug());

            //We add a comment to order
            $comment = Mage::helper('cashway')->__("Transaction is open.");
            //Order is not saved here but after in order payment process
            $order->addStatusToHistory(true, $comment);
        }

        return $this;
    }
    /**
     * Send request to evaluate Transaction method
     * This method can evalute availabilty of cashway api
     *
     * @param Mage_Sales_Model_Quote $quote
     */
    public function evaluateTransaction($quote)
    {
        $params = $this->getOrderParams(new Varien_Object(array('order'=>$quote)));
        $request = Mage::getModel('cashway/api_request', array($this));
        /* @var $request Sirateck_Cashway_Model_Api_Request */
        $response = $request->evaluateTransaction(Sirateck_Cashway_Model_Api_Request::ACTION_EVALUATE_TRANSACTION, $params, $quote->getStoreId());
        $this->_debug($response->debug());

        return $response;
    }

    /**
     * Send event payment failed
     *
     * @param Mage_Sales_Model_Order $lastOrder
     */
    public function sendEventPaymentFailed($lastOrder)
    {
        $params = $this->getEventPaymentFailedParams($lastOrder);
        $request = Mage::getModel('cashway/api_request', array($this));
        /* @var $request Sirateck_Cashway_Model_Api_Request */
        $response = $request->sendEventRequest(Sirateck_Cashway_Model_Api_Request::ACTION_SEND_EVENTS, $params, $lastOrder->getStoreId());
        $this->_debug($response->debug());

        return $response;
    }

    public function getModuleAgent()
    {
        if (!property_exists($this, $cashway_agent)) {
            $this->cashway_agent = sprintf(
                'CashWayModule/%s Magento/%s PHP/%s %s',
                Mage::getConfig()->getModuleConfig("Sirateck_Cashway")->version,
                Mage::getVersion(),
                PHP_VERSION,
                PHP_OS
            );
        }

        return $this->cashway_agent;
    }

    /**
     * Format event payment failed datas before are sended to the API
     * @param Mage_Sales_Model_Order $order
     * array $params
     */
    public function getEventPaymentFailedParams($order)
    {
        $params = array();
        $params['agent'] = $this->getModuleAgent();
        $params["event"] = "payment_failed";

        $orderDate = Mage::app()->getLocale()->storeDate(
            $order->getStore(),
            Varien_Date::toTimestamp($order->getCreatedAt()),
            true
        );
        $params['created_at'] = $orderDate->toString("c");
        $params['provider'] = $order->getPayment()->getMethodInstance()->getCode();
        $params['reason'] = ""; //@TODO maybe send the last history item

        $params['order'] =array();
        $params['order']['id'] = $order->getIncrementId();
        $params['order']['total'] = $order->getBaseGrandTotal();

        $params['customer'] = array();
        $params['customer']['id'] = $order->getCustomerId();
        $params['customer']['email'] = $order->getCustomerEmail();

        $this->_debug($params);

        return $params;
    }

    /**
     * Format order datas before are sended to the API
     * @param Mage_Sales_Model_Order_Payment $payment
     * array $params
     */
    public function getOrderParams($payment)
    {
        $order = $payment->getOrder();

        $orderDate = Mage::app()->getLocale()->storeDate(
            $order->getStore(),
            Varien_Date::toTimestamp($order->getCreatedAt()),
            true
        );//$order->getCreatedAtStoreDate();

        $params = $this->getCustomerParams($payment);
        $params['agent'] = $this->getModuleAgent();

        $params['order'] =array();
        $params['order']['id'] = $order->getIncrementId();
        $params['order']['at'] = $orderDate->toString("c");
        $params['order']['currency'] = $order->getBaseCurrencyCode();
        $params['order']['total'] = $order->getBaseGrandTotal();
        $params['order']['items_count'] = $order->getItemsCollection()->count();

        $details = array();
        foreach ($order->getItemsCollection() as $item) {
            $details[] = array(
                'description' => $item->getName(),
                'quantity' => $item->getQtyOrdered(),
                'price' => $item->getPrice()
            );
        }
        $params['order']['details'] = $details;

        $this->_debug($params);

        return $params;
    }

    /**
     * Prepare order datas before are sended to confirmOrder api method
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return array $params
     */
    public function getConfirmOrderParams($payment)
    {
        $order = $payment->getOrder();

        $orderDate = $order->getCreatedAtStoreDate();
        $params = array();
        $params['agent'] = $this->getModuleAgent();

        $params['order_id'] = $order->getIncrementId();
        $params['email'] = $order->getCustomerEmail();
        $params['phone'] = $order->getBillingAddress()->getTelephone();

        $this->_debug($params);

        return $params;
    }

    /**
     * Format customer datas before are sended to the API
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param array $params
     * @return array $params
     */
    public function getCustomerParams($payment, $params = array())
    {
        $order = $payment->getOrder();
        $params['customer'] = array();
        $params['customer']['id'] = $order->getCustomerId();
        $params['customer']['name'] = $order->getCustomerName();
        $params['customer']['email'] = $order->getCustomerEmail();
        $params['customer']['phone'] = array($order->getBillingAddress()->getTelephone(),$order->getShippingAddress()->getTelephone());
        $params['customer']['country'] = $order->getBillingAddress()->getCountry();
        $params['customer']['city'] = $order->getBillingAddress()->getCity();
        $params['customer']['zipcode'] = $order->getBillingAddress()->getPostcode();
        $params['customer']['address'] = $order->getBillingAddress()->getStreetFull();

        return $params;
    }
}
