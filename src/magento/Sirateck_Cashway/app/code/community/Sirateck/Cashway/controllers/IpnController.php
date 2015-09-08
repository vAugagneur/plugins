<?php

class Sirateck_Cashway_IpnController extends Mage_Core_Controller_Front_Action {

	
	protected $_order = null;
	protected $_data = null;
	
	/**
	 * @return Mage_Core_Controller_Front_Action
	 */
	public function preDispatch() {
		parent::preDispatch();

		if (!$this->_validateSignature()) {
			$this->getResponse()->setBody("NOK. Wrong Signature!");
			$this->setFlag('', 'no-dispatch', true);
		}
		
	}
	
	protected function _validateSignature()
	{
		
		if($this->getData())
		{

			/* @var $order Mage_Sales_Model_Order */
			$order = Mage::getModel('sales/order')->loadByIncrementId($this->getData()->order_id);
				
			if($order->getId())
			{
				$this->_order = $order;
				$method = $order->getPayment()->getMethodInstance();
				$sharedSecret = $this->getConfig()->getApiSharedSecret($order->getStoreId());
				if($method->getConfigData('is_test_mode'))
				{
					$sharedSecret = $this->getConfig()->getApiSharedSecretTest($order->getStoreId());
				}
				
				$signature = explode("=", $this->getRequest()->getServer('HTTP_X_CASHWAY_SIGNATURE'));

				return hash_hmac($signature[0], $this->getRequest()->getRawBody(), $sharedSecret, false) === $signature[1];
				
			}
		}
		
		return false;
	
		
		
	}
	
	public function getData()
	{
		if(is_null($this->_data))
		{
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
	
	public function indexAction()
    {
  		
    	$event = $this->getRequest()->getServer('HTTP_X_CASHWAY_EVENT');

    	if($event == 'conversion_expired')
    	{
    		if($this->getOrder()->canCancel())
    			$this->getOrder()->cancel();
    		$comment = Mage::helper('cashway')->__("Conversion Expired at %s.",$this->getData()->expires_at);
    		$this->getOrder()->addStatusToHistory(true,$comment);
    		$this->getOrder()->save();
    		
    		$this->getResponse()->setBody("OK");
    		return $this;
    		
    	}
    	$res = Mage::getModel('core/resource_transaction');
    	$res->addObject($this->getOrder());
    	
    	switch ($event)
    	{
    		case "transaction_confirmed" :
    			$comment = Mage::helper('cashway')->__("Transaction confimed with status %s.",$this->getData()->status);
    			$this->getOrder()->addStatusToHistory(true,$comment);
    			
    			break;
    		case "transaction_paid":
    				$this->getOrder()->setState(
								Mage_Sales_Model_Order::STATE_PROCESSING,
								true,
								Mage::helper('cashway')->__("Transaction paid at %s.",$this->getData()->paid_at),
								$notified = true);
    				
    				$invoice = $this->getOrder()->prepareInvoice();
    				$invoice->register();
    				$invoice->getOrder()->setIsInProcess(true);
    				$invoice->setIsPaid(1);
						
					if (!$this->getOrder()->getEmailSent()) {
						$this->getOrder()->sendNewOrderEmail();
					}
					
					$res->addObject($invoice);
					
    			break;
    		case "transaction_expired":
    			$this->getOrder()->cancel();
    			$comment = Mage::helper('cashway')->__("Transaction Expired at %s.",$this->getData()->expires_at);
    			$this->getOrder()->addStatusToHistory(true,$comment);
    			break;
    		case "transaction_cancelled":
    			$this->getOrder()->cancel();
    			$comment = Mage::helper('cashway')->__("Transaction Canceled with status %s.",$this->getData()->status);
    			$this->getOrder()->addStatusToHistory(true,$comment);
    			break;
    		case "transaction_blocked":
    			if($this->getOrder()->canHold())
    				$this->getOrder()->hold();
    				
    			$comment = Mage::helper('cashway')->__("Transaction Blocked with status %s.",$this->getData()->status);
    			$this->getOrder()->addStatusToHistory(true,$comment);
    			break;
    		default:
    			$comment = Mage::helper('cashway')->__("Notification received with data %s.",print_r($this->getData(),true));
    			$this->getOrder()->addStatusToHistory(true,$comment);
    			break;
    	}
    	
    	$res->save();
    	
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
