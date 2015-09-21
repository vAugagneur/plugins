<?php
class Sirateck_Cashway_Model_Observer
{
	
	public function updateAccount($observer)
	{
		/* @var $configData Mage_Core_Model_Config_Data */
		$configData = $observer->getConfigData();
		
		if($configData->getGroupId() == 'cashway_api' )
		{
			$value = (string)$configData->getValue();
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
					Mage::log($params,null,'debug_config.log');
					/* @var $request Sirateck_Cashway_Model_Api_Request */
					$request = Mage::getModel('cashway/api_request');
					$request->setData('api_key',$this->getConfig()->getApiKeyTest());
					$request->setData('api_secret',$this->getConfig()->getApiSecretTest());
					
					$accountResponse = $request->updateOrGetAccountRequest(Sirateck_Cashway_Model_Api_Request::ACTION_UPDATE_ACCOUNT, $params);
					
				}

			}
		}
		
		
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