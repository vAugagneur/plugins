<?php
class Sirateck_Cashway_Model_Method_Cashway extends Mage_Payment_Model_Method_Abstract
{
	protected $_code  = 'cashway';
	protected $_formBlockType = 'cashway/form_cashway';
	protected $_infoBlockType = 'cashway/info_cashway';
	
	
	protected $_isInitializeNeeded = true;
	
	public function isAvailable($quote = null)
	{
		return parent::isAvailable($quote);
	}
	
	public function initialize($paymentAction, $stateObject)
	{
		/* @var $payment Mage_Sales_Model_Order_Payment */
		$payment = $this->getInfoInstance();
		$order = $payment->getOrder();
		

		$request = Mage::getModel('cashway/api_request',array($this));
		/* @var $request Sirateck_Cashway_Model_Api_Request */
		$orderResponse = $request->saveOrderRequest(Sirateck_Cashway_Model_Api_Request::ACTION_SAVE_ORDER, $this->getOrderParams($payment),$payment->getOrder()->getStoreId());
		$this->_debug($orderResponse->debug());

		if($orderResponse->getBarcode() != "" && $orderResponse->getStatus() == "open")
		{
			$payment->setAdditionalInformation('cashway_barcode',$orderResponse->getBarcode());
			$confirmResponse = $request->confirmOrderRequest(Sirateck_Cashway_Model_Api_Request::ACTION_CONFIRM_ORDER, $orderResponse->getBarcode(),$this->getConfirmOrderParams($payment),$payment->getOrder()->getStoreId());
			$this->_debug($confirmResponse->debug());
			
			$comment = Mage::helper('cashway')->__("Transaction is open.");
			$order->addStatusToHistory(true,$comment);
			
		}
		
		
	
		return $this;
	
	}
	/**
	 * 
	 * @param Mage_Sales_Model_Quote $quote
	 */
	public function evaluateTransaction($quote)
	{
		$params = $this->getOrderParams(new Varien_Object(array('order'=>$quote)));
		$request = Mage::getModel('cashway/api_request',array($this));
		/* @var $request Sirateck_Cashway_Model_Api_Request */
		$response = $request->evaluateTransaction(Sirateck_Cashway_Model_Api_Request::ACTION_EVALUATE_TRANSACTION, $params,$quote->getStoreId());
		$this->_debug($response->debug());
		
		return $response;
	}
	
	/**
	 * 
	 * @param Mage_Sales_Model_Order_Payment $payment
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
		$params['order'] =array(); 
		$params['order']['id'] = $order->getIncrementId();
		$params['order']['at'] = $orderDate->toString("c");
		$params['order']['currency'] = $order->getBaseCurrencyCode();
		$params['order']['total'] = $order->getBaseGrandTotal();
		
		$this->_debug($params);
		
		return $params;
	}
	
	public function getConfirmOrderParams($payment)
	{
		$order = $payment->getOrder();
		
		$orderDate = $order->getCreatedAtStoreDate();
		$params = array();
		$params['order_id'] = $order->getIncrementId();
		$params['email'] = $order->getCustomerEmail();
		$params['phone'] = $order->getBillingAddress()->getTelephone();
		
		$this->_debug($params);
		
		return $params;
	}
	
	/**
	 *
	 * @param Mage_Sales_Model_Order_Payment $payment
	 * @param array $params
	 * @return array $params
	 */
	public function getCustomerParams($payment,$params=array())
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