<?php
/**
 * 2015 CashWay - Epayment Solution
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    hupstream <mailbox@hupstream.com>
 *  @copyright 2015 Epayment Solution
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * @since 1.5.0
 */
class CashwayPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;
        if (!$this->module->checkCurrency($cart)) {
            Tools::redirect('index.php?controller=order');
        }

        $currency = $this->module->getCurrency((int)$this->context->cart->id_currency);
        $cashway = CashWay::getCashWayAPI();
        $cashway->setOrder(
            'prestashop',
            null,
            $this->context->cart,
            $this->context->customer,
            $this->context->language->iso_code,
            $currency[0]['iso_code']
        );

        $available = array(true, '');

        // fire & forget at this point
        $cw_res = $cashway->evaluateTransaction();

        if (array_key_exists('errors', $cw_res)) {
            $available = array(false);
            switch ($cw_res['errors'][0]['code']) {
                case 'no_such_user':
                    $available[] = ''; //'<!-- CW debug: unknown user -->';
                    break;
                case 'unavailable':
                    $available[] = ''; //'<!-- CW debug: API unavailable -->';
                    break;
                default:
                    $available[] = ''; //'<!-- CW debug: unknown -->';
                    break;
            }
            $available[] = $cw_res['errors'][0]['code'];
        }

        // Limited to France for now
        $address  = new Address($cart->id_address_delivery);
        $country = new Country($address->id_country);
        if ($country->iso_code != 'FR') {
            $available = array(false,
                $this->module->l('This service is only available in France for the time being.'));
        }

        $location = array(
            'address' => $address->address1,
            'postcode' => $address->postcode,
            'city' => $address->city,
            'country' => $address->country
        );
        $location['search'] = implode(' ', $location);
        $this->context->smarty->assign(array(
            'available' => $available,
            'cart_fee' => number_format(
                $cashway->getCustomerFees($cart->getOrderTotal()),
                2,
                '.',
                '&nbsp;'
            ),
            'location' => $location,
            'nbProducts' => $this->context->cart->nbProducts(),
            'total' => $this->context->cart->getOrderTotal(true, Cart::BOTH),
            'this_path_cashway' => $this->module->getPathUri(),
            'cashway_api_base_url' => $cashway->api_base_url,
            'kyc_conditions' => (array_key_exists('conditions', $cw_res) ? $cw_res['conditions'] : null),
            'kyc_upload_url' => \CashWay\API_URL.\CashWay\KYC_PATH,
            'kyc_upload_mail' => \CashWay\KYC_MAIL
        ));

        $this->setTemplate('payment_execution.tpl');
    }
}
