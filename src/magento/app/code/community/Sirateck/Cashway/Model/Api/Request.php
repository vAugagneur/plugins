<?php
class Sirateck_Cashway_Model_Api_Request extends Varien_Object
{
	
	
 	
	/**
	 *
	 * @var Zend_Http_Client
	 */
	protected $_client = null;
	
	protected $_methodInstance = null;
	
	protected $_storeId = null;
	
	const ACTION_UPDATE_ACCOUNT = 'update_account';
	
	const ACTION_GET_ACCOUNT = 'get_account';
	
	const ACTION_SAVE_ORDER = 'save_order';
	
	const ACTION_CONFIRM_ORDER = 'confirm_order';
	
	const ACTION_EVALUATE_TRANSACTION = 'evaluate_transaction';
	
	public function __construct( $methodInstance = array())
	{
		if(count($methodInstance) > 0)
			$this->_methodInstance = $methodInstance[0];
	}
	
	protected function getMethodInstance()
	{
		if(!$this->_methodInstance instanceof Mage_Payment_Model_Method_Abstract)
			Mage::throwException("Method instance must be setted or must be type of Mage_Payment_Model_Method_Abstract");
	
		return $this->_methodInstance;
	}
	
	/**
	 *
	 * @param Mage_Payment_Model_Method_Abstract $methodInstance
	 */
	protected function setMethodInstance($methodInstance)
	{
		$this->_methodInstance = $methodInstance;
	}
	
	
	protected function getApiKey($storeId=null)
	{
		
		if($this->getData('api_key') != "")
			return $this->getData('api_key');
		
		if($this->isTestMode())
			return $this->getConfig()->getApiKeyTest($storeId);
	
		return $this->getConfig()->getApiKey($storeId);
	}
	
	protected function getApiSecret($storeId=null)
	{
		if($this->getData('api_secret') != "")
			return $this->getData('api_secret');
		
		if($this->isTestMode())
			return $this->getConfig()->getApiSecretTest($storeId);
	
		return $this->getConfig()->getApiSecret($storeId);
	}
	
	protected function getApiSharedSecret($storeId=null)
	{
		if($this->isTestMode())
			return $this->getConfig()->getApiSharedSecretTest($storeId);
	
		return $this->getConfig()->getApiSharedSecret($storeId);
	}
	
	protected function isTestMode()
	{
		return (bool)$this->getMethodInstance()->getConfigData('is_test_mode');
	}
	
	
	
	/**
	 *
	 * @return Sirateck_Cashway_Model_Config $config
	 */
	protected function getConfig()
	{
		return Mage::getSingleton('cashway/config');
	}
	
	/**
	 * Get client HTTP
	 * @return Zend_Http_Client
	 */
	public function getClient()
	{
		if(is_null($this->_client))
		{
			//adapter options
			$config = array('curloptions' => array(
					CURLOPT_RETURNTRANSFER=>true,
					CURLOPT_TIMEOUT=>5,
					CURLOPT_CONNECTTIMEOUT =>5,
					CURLOPT_FORBID_REUSE   => true,
            		CURLOPT_SSL_VERIFYPEER => true,
            		CURLOPT_SSL_VERIFYHOST => 2,),
			);
			try {
				
				//innitialize http client and adapter curl
				$adapter = Mage::getSingleton('cashway/api_http_client_adapter_curl');
	
				$this->_client = new Zend_Http_Client();
				$this->_client->setConfig($config);
				$this->_client->setHeaders(array('Content-Type'=> 'application/json',
												'Accept'=>'application/json'));
				$this->_client->setAuth($this->getApiKey($this->getStoreId()),
						$this->getApiSecret($this->getStoreId()),
						Zend_Http_Client::AUTH_BASIC);
				$this->_client->setAdapter($adapter);
	
	
			} catch (Exception $e) {
				Mage::throwException($e);
			}
		}
	
		return $this->_client;
	}
	
	protected function _request($uri,$params=array(),$method=Zend_Http_Client::POST,$storeId=null)
	{
	
		if(count($params)>0)
		{
			
			if($method == Zend_Http_Client::POST)
				$this->getClient()->setRawData(json_encode($params));
			else
				$this->getClient()->setParameterGet($params);
		}
	
		$this->getClient()->setUri($uri);
	
		/* @var $response Zend_Http_Response */
		$response = $this->getClient()->request($method);
	
		if($response->isSuccessful())
		{
			//$this->getClient()->getAdapter()->close();
			return json_decode($response->getBody(),true);
		}
		else
		{
			/* @var $error Sirateck_CashWay_Model_Api_Response_Error */
			$error = Mage::getSingleton('cashway/api_response_error');
			$messageError = "Status: " . $response->getStatus() . ". Message: " . $response->getMessage()." Uri: ".$uri;
			$res = (json_decode($response->getBody(),true));
			if($res && count($res)>0)
			{
				
				$error->setData(current($res['errors']));
				$messageError = "Status: " . $error->getStatus() . ". Message: " . $error->getMessage()." Uri: ".$uri;
				if(trim($error->getCode()) != "")
					$messageError .= ". Code: " . $error->getCode();
			}
			
			
			Mage::throwException($messageError);
		}
			
	
	}
	
	public function getMethodHttp($action)
	{
		$actionsPost = array(self::ACTION_SAVE_ORDER,
							self::ACTION_CONFIRM_ORDER,
							self::ACTION_EVALUATE_TRANSACTION,
							self::ACTION_UPDATE_ACCOUNT,
		);
		if(in_array($action, $actionsPost))
			return Zend_Http_Client::POST;
	
		return Zend_Http_Client::GET;
	}
	
	/**
	 *
	 */
	protected function getOrderApiEndpoint($storeId=null) {
	
		return $this->getConfig()->getOrderApiEndpoint($storeId);
	
	}
	
	/**
	 *
	 */
	protected function getAccountUpdateApiEndpoint($storeId=null) {
	
		return $this->getConfig()->getAccountUpdateApiEndpoint($storeId);
	
	}
	
	/**
	 *
	 */
	protected function getConfirmOrderApiEndpoint($storeId=null) {
	
		return $this->getConfig()->getConfirmOrderApiEndpoint($storeId);
	
	}
	
	protected function getEvaluateTransactionApiEndpoint($storeId=null) {
	
		return $this->getConfig()->getEvaluateTransactionApiEndpoint($storeId);
	
	}

	
	
	/**
	 *
	 * @param string $action
	 * @param array $params
	 * @param int $storeId
	 * @return Sirateck_Cashway_Model_Response_Order
	 */
	public function confirmOrderRequest($action,$barcode,$params,$storeId=null)
	{
		$this->setStoreId($storeId);
		$uri = str_replace('$ID', $barcode, $this->getConfirmOrderApiEndpoint($storeId));
	
		/* @var $response Sirateck_Cashway_Model_Response_Order */
		$response = Mage::getSingleton('cashway/api_response_order', $this->_request($uri,$params,$this->getMethodHttp($action),$storeId));
	
		return $response;
	}
	
	/**
	 *
	 * @param string $action
	 * @param array $params
	 * @param int $storeId
	 * @return Sirateck_Cashway_Model_Response_Order
	 */
	public function saveOrderRequest($action,$params,$storeId=null)
	{
		$this->setStoreId($storeId);
		$uri = $this->getOrderApiEndpoint($storeId);
	
		/* @var $response Sirateck_Cashway_Model_Response_Order */
		$response = Mage::getSingleton('cashway/api_response_order', $this->_request($uri,$params,$this->getMethodHttp($action),$storeId));
	
		return $response;
	}
	
	/**
	 *
	 * @param string $action
	 * @param array $params
	 * @param int $storeId
	 * @return Sirateck_Cashway_Model_Response_Account
	 */
	public function updateOrGetAccountRequest($action,$params,$storeId=null)
	{
		$this->setStoreId($storeId);
		$uri = $this->getAccountUpdateApiEndpoint($storeId);
	
		/* @var $response Sirateck_Cashway_Model_Response_Account */
		$response = Mage::getSingleton('cashway/api_response_account', $this->_request($uri,$params,$this->getMethodHttp($action),$storeId));
	
		return $response;
	}
	
	
	public function evaluateTransaction($action,$params,$storeId=null)
	{
		$this->setStoreId($storeId);
		$uri = $this->getEvaluateTransactionApiEndpoint($storeId);
		
		/* @var $response Sirateck_Cashway_Model_Response_Order */
		$response = Mage::getSingleton('cashway/api_response_order', $this->_request($uri,$params,$this->getMethodHttp($action),$storeId));
		
		return $response;
		
	}
	
	public function setStoreId($storeId)
	{
		$this->_storeId = $storeId;
		return $this;
	}
	
	public function getStoreId()
	{
		return $this->_storeId;
	}
	
}