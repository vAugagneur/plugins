<?php
class Sirateck_Cashway_Block_Form_Cashway extends Mage_Payment_Block_Form
{
	
	protected $_evaluateTransaction = null;
	
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('cashway/form/cashway.phtml');
    }
    
    public function getEvaluateTransaction()
    {
    	if(is_null($this->_evaluateTransaction))
    	{
    		/* @var $methodInstance Sirateck_Cashway_Model_Method_Cashway */
    		$methodInstance = $this->getMethod();

    		$this->_evaluateTransaction = $methodInstance->evaluateTransaction($this->getQuote());
    		
    		
    	}
    	
    	return $this->_evaluateTransaction;
    }
    
    public function canWork()
    {
    	$cw_res = $this->getEvaluateTransaction();
    	
    	if (array_key_exists('errors', $cw_res))
    	{
    		$available = array(false);
    		switch ($cw_res['errors'][0]['code'])
    		{
    			case 'no_such_user':
    				$available[] = '<!-- CW debug: unknown user -->';
    				break;
    			case 'unavailable':
    				$available[] = '<!-- CW debug: API unavailable -->';
    				break;
    			default:
    				$available[] = '<!-- CW debug: unknown -->';
    				break;
    		}
    		return false;
    	}
    	return true;
    }
    
    public function getAdressOneLine()
    {
    	return $this->getQuote()->getShippingAddress()->format('oneline');
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
