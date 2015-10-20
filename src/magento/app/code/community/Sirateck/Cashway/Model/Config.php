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
 * 
 * This config object manage configuration of cashway module
 * 
 * @author Kassim Belghait <kassim@sirateck.com>
 */
class Sirateck_Cashway_Model_Config extends Varien_Object
{
	const API_VERSION = 1;
	
	const API_KEY = 'api_key';
	
	const API_SECRET = 'api_secret';
	
	const API_SHARED_SECRET = 'api_shared_secret';
	
	const API_KEY_TEST = "api_key_test";
	
	const API_SECRET_TEST = 'api_secret_test';
	
	const API_SHARED_SECRET_TEST = 'api_shared_secret_test';
	
	const API_BASE_URI = 'api_base_uri';
	
	const API_BASE_URI_TEST = 'api_base_uri_test';
	
	const API_IPN_PATH = 'api_ipn_path';
	
	const API_ACCOUNT_UPDATE_ENDPOINT = "account_update_api_endpoint";
	
	const API_ORDER_ENDPOINT = 'order_api_endpoint';
	
	const API_CONFIRM_ORDER_ENDPOINT = 'confirm_order_api_endpoint';
	
	const API_EVALUATE_TRANSACTION_ENDPOINT = 'evaluate_transaction_api_endpoint';
	
	/**
	 *  Return config var
	 *
	 *  @param    string $key Var path key
	 *  @param    int $storeId Store View Id
	 *  @return	  mixed
	 */
	public function getConfigData($key, $storeId = null)
	{
		
		if (!$this->hasData($key)) {
			$value = Mage::getStoreConfig('cashway/cashway_api/' . $key, $storeId);
			$this->setData($key, $value);
		}
		return $this->getData($key);
	}
	
	/**
	 *  Return config var
	 *
	 *  @param    string $key Var path key
	 *  @param    int $storeId Store View Id
	 *  @return	  mixed
	 */
	public function getConfigFlag($key, $storeId = null)
	{
		
		if (!$this->hasData($key)) {
			$value = Mage::getStoreConfigFlag('cashway/cashway_api/' . $key, $storeId);
			$this->setData($key, $value);
		}
		return $this->getData($key);
	}
	
	
	public function isTestMode()
	{
		return (bool)Mage::getStoreConfigFlag('payment/cashway/is_test_mode');
	}
	
	
	public function getApiKey($storeId =null)
	{
		return $this->getConfigData(self::API_KEY,$storeId);
	}
	
	public function getApiSecret($storeId=null)
	{
		return $this->getConfigData(self::API_SECRET,$storeId);
	}
	
	public function getApiSharedSecret($storeId=null)
	{
		return $this->getConfigData(self::API_SHARED_SECRET,$storeId);
	}
	
	
	public function getApiKeyTest($storeId =null)
	{
		return $this->getConfigData(self::API_KEY_TEST,$storeId);
	}
	
	public function getApiSecretTest($storeId=null)
	{
		return $this->getConfigData(self::API_SECRET_TEST,$storeId);
	}
	
	public function getApiSharedSecretTest($storeId=null)
	{
		return $this->getConfigData(self::API_SHARED_SECRET_TEST,$storeId);
	}
	
	public function getApiBaseUri($storeId=null)
	{
		if($this->isTestMode())
			return $this->getConfigData(self::API_BASE_URI_TEST) . self::API_VERSION;
		
		return $this->getConfigData(self::API_BASE_URI) . self::API_VERSION;
	}
	
	public function getIpnUrl($storeId=null)
	{
		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB,true) . $this->getConfigData(self::API_IPN_PATH);
	}
	
	public function getAccountUpdateApiEndpoint($storeId = null)
	{
		$uri = $this->getApiBaseUri().$this->getConfigData(self::API_ACCOUNT_UPDATE_ENDPOINT,$storeId);
		return $uri;
	}
	
	public function getOrderApiEndpoint($storeId = null)
	{
		$uri = $this->getApiBaseUri().$this->getConfigData(self::API_ORDER_ENDPOINT,$storeId);
		return $uri;
	}
	
	public function getConfirmOrderApiEndpoint($storeId = null)
	{
		$uri = $this->getApiBaseUri().$this->getConfigData(self::API_CONFIRM_ORDER_ENDPOINT,$storeId);
		return $uri;
	}
	
	public function getEvaluateTransactionApiEndpoint($storeId = null)
	{
		$uri = $this->getApiBaseUri().$this->getConfigData(self::API_EVALUATE_TRANSACTION_ENDPOINT,$storeId);
		return $uri;
	}
	
}