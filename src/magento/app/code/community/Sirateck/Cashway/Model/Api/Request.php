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
 * Request Object
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 */
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

    const ACTION_SEND_EVENTS = 'send_events';

    const ACTION_GET_CUSTOMER_FEES = 'get_customer_fees';

    public function __construct($methodInstance = array())
    {
        if (count($methodInstance) > 0) {
            $this->_methodInstance = $methodInstance[0];
        }
    }

    protected function getMethodInstance()
    {
        if (!$this->_methodInstance instanceof Mage_Payment_Model_Method_Abstract) {
            Mage::throwException("Method instance must be setted or must be type of Mage_Payment_Model_Method_Abstract");
        }

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

    protected function getApiKey($storeId = null)
    {

        if ($this->getData('api_key') != "") {
            return $this->getData('api_key');
        }

        if ($this->isTestMode()) {
            return $this->getConfig()->getApiKeyTest($storeId);
        }

        return $this->getConfig()->getApiKey($storeId);
    }

    protected function getApiSecret($storeId = null)
    {
        if ($this->getData('api_secret') != "") {
            return $this->getData('api_secret');
        }

        if ($this->isTestMode()) {
            return $this->getConfig()->getApiSecretTest($storeId);
        }

        return $this->getConfig()->getApiSecret($storeId);
    }

    protected function getApiSharedSecret($storeId = null)
    {
        if ($this->isTestMode()) {
            return $this->getConfig()->getApiSharedSecretTest($storeId);
        }

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
     * Get Zend client HTTP
     * @return Zend_Http_Client
     */
    public function getClient()
    {
        if (is_null($this->_client)) {
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
                //Set headers for JSON
                $this->_client->setHeaders(array('Content-Type'=> 'application/json',
                                                'Accept'=>'application/json'));

                //Set authentication
                $this->_client->setAuth(
                    $this->getApiKey($this->getStoreId()),
                    $this->getApiSecret($this->getStoreId()),
                    Zend_Http_Client::AUTH_BASIC
                );

                //Set Curl adapter to http client
                $this->_client->setAdapter($adapter);
            } catch (Exception $e) {
                Mage::throwException($e);
            }
        }

        return $this->_client;
    }

    /**
     * Construct and send request to cashway API
     *
     * @param string $uri
     * @param array $params
     * @param string $method
     * @param mixed $storeId
     */
    protected function _request($uri, $params = array(), $method = Zend_Http_Client::POST, $storeId = null)
    {
        if (count($params)>0) {
        //If http method is POST we set params in RawBody
            if ($method == Zend_Http_Client::POST) {
                $this->getClient()->setRawData(json_encode($params));
            } else { // Else in get parameters
                $this->getClient()->setParameterGet($params);
            }
        }

        //Set the uri endpoint
        $this->getClient()->setUri($uri);

        /* @var $response Zend_Http_Response */
        $response = $this->getClient()->request($method);

        return json_decode($response->getBody(), true);

        if ($response->isSuccessful()) {
            return json_decode($response->getBody(), true);
        } else {
            /* @var $error Sirateck_CashWay_Model_Api_Response_Error */
            $error = Mage::getSingleton('cashway/api_response_error');
            $messageError = "Status: " . $response->getStatus() . ". Message: " . $response->getMessage()." Uri: ".$uri;
            $res = (json_decode($response->getBody(), true));
            if ($res && count($res)>0) {
                $error->setData(current($res['errors']));
                $messageError = "Status: " . $error->getStatus() . ". Message: " . $error->getMessage()." Uri: ".$uri;
                if (trim($error->getCode()) != "") {
                    $messageError .= ". Code: " . $error->getCode();
                }
            }

            Mage::throwException($messageError);
        }
    }

    /**
     * Return the method of an action
     * @param string $action
     * @return string Http method
     */
    public function getMethodHttp($action)
    {
        $actionsPost = array(self::ACTION_SAVE_ORDER,
                            self::ACTION_CONFIRM_ORDER,
                            self::ACTION_EVALUATE_TRANSACTION,
                            self::ACTION_UPDATE_ACCOUNT,
                            self::ACTION_SEND_EVENTS
        );
        if (in_array($action, $actionsPost)) {
            return Zend_Http_Client::POST;
        }

        return Zend_Http_Client::GET;
    }

    /**
     * @param mixed storeId
     * @return string $orderApiEndpoint
     */
    protected function getOrderApiEndpoint($storeId = null)
    {
        return $this->getConfig()->getOrderApiEndpoint($storeId);
    }

    /**
     * @param mixed storeId
     * @return string $EventsApiEndpoint
     */
    protected function getEventsApiEndpoint($storeId = null)
    {
        return $this->getConfig()->getEventsApiEndpoint($storeId);
    }

    /**
     * @param mixed storeId
     * @return string $accountUpdateApiEndpoint
     */
    protected function getAccountUpdateApiEndpoint($storeId = null)
    {
        return $this->getConfig()->getAccountUpdateApiEndpoint($storeId);
    }

    /**
     * @param mixed storeId
     * @return string $confirmOrderApiEndpoint
     */
    protected function getConfirmOrderApiEndpoint($storeId = null)
    {
        return $this->getConfig()->getConfirmOrderApiEndpoint($storeId);
    }

    /**
     * @param mixed storeId
     * @return string $evaluateTransactionApiEndpoint
     */
    protected function getEvaluateTransactionApiEndpoint($storeId = null)
    {
        return $this->getConfig()->getEvaluateTransactionApiEndpoint($storeId);
    }

    /**
     * @param mixed storeId
     * @return string $getCustomerFeesApiEndpoint
     */
    protected function getGetCustomerFeesApiEndpoint($storeId = null)
    {
        return $this->getConfig()->getGetCustomerFeesApiEndpoint($storeId);
    }

    /**
     * Construct and send confirmOrder request to cashway API
     *
     * @param string $action
     * @param array $params
     * @param int $storeId
     * @return Sirateck_Cashway_Model_Response_Order
     */
    public function confirmOrderRequest($action, $barcode, $params, $storeId = null)
    {
        $this->setStoreId($storeId);
        $uri = str_replace('$ID', $barcode, $this->getConfirmOrderApiEndpoint($storeId));

        /* @var $response Sirateck_Cashway_Model_Response_Order */
        $response = Mage::getSingleton('cashway/api_response_order', $this->_request($uri, $params, $this->getMethodHttp($action), $storeId));

        return $response;
    }

    /**
     * Construct and send order (transaction) request to cashway API
     *
     * @param string $action
     * @param array $params
     * @param int $storeId
     * @return Sirateck_Cashway_Model_Response_Order
     */
    public function saveOrderRequest($action, $params, $storeId = null)
    {
        $this->setStoreId($storeId);
        $uri = $this->getOrderApiEndpoint($storeId);

        /* @var $response Sirateck_Cashway_Model_Response_Order */
        $response = Mage::getSingleton('cashway/api_response_order', $this->_request($uri, $params, $this->getMethodHttp($action), $storeId));

        return $response;
    }

    /**
     * Construct and send updateOrGetAccount request to cashway API
     *
     * @param string $action
     * @param array $params
     * @param int $storeId
     * @return Sirateck_Cashway_Model_Response_Account
     */
    public function updateOrGetAccountRequest($action, $params, $storeId = null)
    {
        $this->setStoreId($storeId);
        $uri = $this->getAccountUpdateApiEndpoint($storeId);

        /* @var $response Sirateck_Cashway_Model_Response_Account */
        $response = Mage::getSingleton('cashway/api_response_account', $this->_request($uri, $params, $this->getMethodHttp($action), $storeId));

        return $response;
    }

    /**
     * Construct and send evaluateTransaction request to cashway API
     *
     * @param string $action
     * @param array $params
     * @param int $storeId
     * @return Sirateck_Cashway_Model_Response_Order
     */
    public function evaluateTransaction($action, $params, $storeId = null)
    {
        $this->setStoreId($storeId);
        $uri = $this->getEvaluateTransactionApiEndpoint($storeId);

        /* @var $response Sirateck_Cashway_Model_Response_Order */
        $response = Mage::getSingleton('cashway/api_response_order', $this->_request($uri, $params, $this->getMethodHttp($action), $storeId));

        return $response;
    }

    /**
     * Get the customer fees from the order's total amount
     *
     * @param string $action
     * @param int $storeId
     * @return Sirateck_Cashway_Model_Response_Order
     */
    public function getCustomerFees($action, $storeId) {
        $this->setStoreId($storeId);
        $uri = $this->getGetCustomerFeesApiEndpoint($storeId);

        /* @var $response Sirateck_Cashway_Model_Response_Fees */
        $response = Mage::getSingleton('cashway/api_response_fees', $this->_request($uri, array(), $this->getMethodHttp($action), $storeId));
        
        return $response;
    }

    /**
     * Send an event
     *
     * @param string $action
     * @param array $params
     * @param int $storeId
     * @return Sirateck_Cashway_Model_Response_Order
     */
    public function sendEventRequest($action, $params, $storeId = null)
    {
        $this->setStoreId($storeId);
        $uri = $this->getEventsApiEndpoint($storeId);

        /* @var $response Sirateck_Cashway_Model_Response_Order */
        $response = Mage::getSingleton('cashway/api_response_order', $this->_request($uri, $params, $this->getMethodHttp($action), $storeId));

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
