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
		
		if($configData->getGroupId() == 'cashway_api' )//Check for configuration group
		{
			$value = (string)$configData->getValue();// Get crypted value
			//Decrypt it, if is not empty
			if (!empty($value) && ($decrypted = Mage::helper('core')->decrypt($value))) {
				$value = $decrypted;
			}
			
			if($configData->getField() == 'api_shared_secret')
			{
				if($this->getConfig()->getApiKey() != "" && $this->getConfig()->getApiSecret() != "")
				{
					$params = array();
					$params['notification_url'] = Mage::getSingleton('cashway/config')->getIpnUrl();
					$params['shared_secret'] = $value;
					
					/* @var $request Sirateck_Cashway_Model_Api_Request */
					$request = Mage::getModel('cashway/api_request');
					$request->setData('api_key',$this->getConfig()->getApiKey());
					$request->setData('api_secret',$this->getConfig()->getApiSecret());
					
					//Send new datas to cashway
					$accountResponse = $request->updateOrGetAccountRequest(Sirateck_Cashway_Model_Api_Request::ACTION_UPDATE_ACCOUNT, $params);
					
				}

			}
			elseif($configData->getField() == 'api_shared_secret_test')
			{

				if($this->getConfig()->getApiKeyTest() != "" && $this->getConfig()->getApiSecretTest() != "")
				{
					$params = array();
					$params['notification_url'] = Mage::getSingleton('cashway/config')->getIpnUrl();
					$params['shared_secret'] = $value;
	
					/* @var $request Sirateck_Cashway_Model_Api_Request */
					$request = Mage::getModel('cashway/api_request');
					$request->setData('api_key',$this->getConfig()->getApiKeyTest());
					$request->setData('api_secret',$this->getConfig()->getApiSecretTest());
					
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