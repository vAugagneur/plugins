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
 * Block Reorder
 * @author Kassim Belghait <kassim@sirateck.com>
 */
class Sirateck_Cashway_Block_Reorder extends Mage_Sales_Block_Items_Abstract
{
    /**
     * Get multishipping checkout model
     *
     * @return Mage_Checkout_Model_Type_Multishipping
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    protected function _prepareLayout()
    {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle(
                $this->__('Review Order - %s', $headBlock->getDefaultTitle())
            );
        }
        return parent::_prepareLayout();
    }

    /**
     *
     * @return Mage_Checkout_Helper_Data
     */
    public function getCheckoutHelper()
    {
        return Mage::helper('checkout');
    }

    public function getPreviousMethodTitle()
    {
        return $this->getCheckout()->getLastPaymentMethodTitle();
    }

    public function getBillingAddress()
    {
        return $this->getCheckout()->getQuote()->getBillingAddress();
    }

    public function getPaymentHtml()
    {
        return $this->getChildHtml('payment_info');
    }

    public function getShippingAddress()
    {
        return $this->getCheckout()->getQuote()->getShippingAddress();
    }

    public function getShippingAddressRate($address)
    {
        if ($rate = $address->getShippingRateByCode($address->getShippingMethod())) {
            return $rate;
        }
        return false;
    }

    public function getShippingPriceInclTax($address)
    {
        $exclTax = $address->getShippingAmount();
        $taxAmount = $address->getShippingTaxAmount();
        return $this->formatPrice($exclTax + $taxAmount);
    }

    public function getShippingPriceExclTax($address)
    {
        return $this->formatPrice($address->getShippingAmount());
    }

    public function formatPrice($price)
    {
        return $this->getQuote()->getStore()->formatPrice($price);
    }

    public function getItems()
    {
        return $this->getCheckout()->getQuote()->getAllVisibleItems();
    }


    public function getPostActionUrl()
    {
        return $this->getUrl('*/*/reorderPost');
    }

    public function getTotal()
    {
        return $this->getCheckout()->getQuote()->getGrandTotal();
    }

    public function getBackUrl()
    {
        return $this->getUrl('checkout/cart');
    }

    /**
     * Retrieve quote
     *
     * @return Mage_Sales_Model_Qoute
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    public function getBillinAddressTotals()
    {
        $_address = $this->getQuote()->getBillingAddress();
        return $this->getShippingAddressTotals($_address);
    }

    public function renderTotals($totals, $colspan = null)
    {
        if ($colspan === null) {
            $colspan = $this->helper('tax')->displayCartBothPrices() ? 5 : 3;
        }
        $totals = $this->getChild('totals')->setTotals($totals)->renderTotals('', $colspan)
            . $this->getChild('totals')->setTotals($totals)->renderTotals('footer', $colspan);
        return $totals;
    }
}
