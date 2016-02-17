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
 * Reorder controller
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 */

class Sirateck_Cashway_ReorderController extends Mage_Checkout_Controller_Action
{
    /**
     * Predispatch
     *
     * @return Sirateck_Cashway_ReorderController
     */
    /**
     * Predispatch: should set layout area
     *
     * @return Mage_Checkout_OnepageController
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $this->_preDispatchValidateCustomer();

        return $this;
    }

    /**
     * Returns whether the minimum amount has been reached
     *
     * @return bool
     */
    protected function _validateMinimumAmount()
    {
        if (!$this->_getCheckout()->validateMinimumAmount()) {
            $error = $this->_getCheckout()->getMinimumAmountError();
            $this->_getCheckout()->getCheckoutSession()->addError($error);
            $this->_redirect('checkout/cart');
            return false;
        }
        return true;
    }

    protected function _getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    protected function getQuote()
    {
        return $this->_getCheckout()->getQuote();
    }

    public function indexAction()
    {
        $lastQuoteId = $this->_getCheckout()->getLastQuoteId();
        $lastOrderId = $this->_getCheckout()->getLastOrderId();

        //die('QuoteId: '.$lastQuoteId . " OrderId: " . $lastOrderId);

        if (!$lastQuoteId || !$lastOrderId) {
            $this->_redirect('checkout/cart');
            return;
        }

        $lastQuoteId = $this->_getCheckout()->getQuoteId();

        //die("qId: " . $lastQuoteId);

        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
    }

    /**
     * Reorder checkout after the overview page
     */
    public function reorderPostAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*');
            return;
        }

        $result = array();
        try {
            $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
            if ($requiredAgreements) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                $diff = array_diff($requiredAgreements, $postedAgreements);
                if ($diff) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    return;
                }
            }

            $data = array('method'=>'cashway');
            if ($data) {
                $data['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
                    | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
                    | Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
                    | Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
                    | Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
                $this->getQuote()->getPayment()->importData($data);
            }

            $this->saveOrder();

            $this->_getCheckout()->unsLastPaymentMethodTitle();

            $this->_redirect('checkout/onepage/success');

        } catch (Mage_Payment_Model_Info_Exception $e) {
            $message = $e->getMessage();
            if (!empty($message)) {
                $this->_getCheckout()->addError($message);
            }
            $this->_redirect('*/*/index');

        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getQuote(), $e->getMessage());
            $this>_getCheckout()->addError($e->getMessage());

            $this->_redirect('checkout/cart');

        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getQuote(), $e->getMessage());

            $this->_getCheckout()->addError($this->__('There was an error processing your order. Please contact us or try again later.'));

            $this->_redirect('checkout/cart');
        }
        $this->getQuote()->save();

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

    public function saveOrder()
    {
        $service = Mage::getModel('sales/service_quote', $this->getQuote());
        $service->submitAll();

        $this->_getCheckout()->setLastQuoteId($this->getQuote()->getId())
        ->setLastSuccessQuoteId($this->getQuote()->getId())
        ->clearHelperData();

        $order = $service->getOrder();
        if ($order) {
            Mage::dispatchEvent(
                'checkout_type_onepage_save_order_after',
                array('order'=>$order, 'quote'=>$this->getQuote())
            );

            if ($order->getCanSendNewEmailFlag()) {
                try {
                    $order->queueNewOrderEmail();
                } catch (Exception $e) {
                    Mage::logException($e);
                }
            }

            // add order information to the session
            $this->_getCheckout()->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId());

            // as well a billing agreement can be created
            $agreement = $order->getPayment()->getBillingAgreement();
            if ($agreement) {
                $this->_getCheckout()->setLastBillingAgreementId($agreement->getId());
            }
        }

        // add recurring profiles information to the session
        $profiles = $service->getRecurringPaymentProfiles();
        if ($profiles) {
            $ids = array();
            foreach ($profiles as $profile) {
                $ids[] = $profile->getId();
            }
            $this->_getCheckout()->setLastRecurringProfileIds($ids);
            // TODO: send recurring profile emails
        }

        Mage::dispatchEvent(
            'checkout_submit_all_after',
            array('order' => $order, 'quote' => $this->getQuote(), 'recurring_profiles' => $profiles)
        );

        return $this;
    }
}
