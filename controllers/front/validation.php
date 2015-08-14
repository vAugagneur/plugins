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
class CashwayValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $cart = $this->context->cart;

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 ||
            $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        // Check that this payment option is still available
        // in case the customer changed his address
        // just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'cashway') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'validation'));
        }

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        if (Tools::getValue('cgu-accept') === false) {
            Tools::redirect('index.php?fc=module&module=cashway&controller=payment&cgu=1');
        }

        $currency = $this->context->currency;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);

        $cw_currency = $this->module->getCurrency((int)$this->context->cart->id_currency);
        $cashway = CashWay::getCashWayAPI();
        $cashway->setOrder(
            'prestashop',
            null,
            $this->context->cart,
            $this->context->customer,
            $this->context->language->iso_code,
            $cw_currency[0]['iso_code'],
            array(
                'button_type' => Tools::getValue('btn', '?')
            )
        );

        $cw_res = $cashway->openTransaction();

        if (array_key_exists('errors', $cw_res)) {
        // error message is in $cw_res['errors'][0]['code']);
            $cw_barcode = '-failed-';
        } else {
            $cw_barcode = $cw_res['barcode'];
        }

        $mail_vars = array(
            '{barcode}' => $cw_barcode,
        );

        if ($cw_barcode != '-failed-') {
            $this->module->validateOrder(
                (int)$cart->id,
                Configuration::get('PS_OS_CASHWAY'),
                $total,
                $this->module->displayName,
                null,
                $mail_vars,
                (int)$currency->id,
                false,
                $customer->secure_key
            );
        }

        Tools::redirect(
            'index.php?controller=order-confirmation&id_cart='.(int)$cart->id
            .'&id_module='.(int)$this->module->id
            // @codingStandardsIgnoreStart
            .'&id_order='.$this->module->currentOrder
            // @codingStandardsIgnoreStop
            .'&cw_barcode='.$cw_barcode
            .'&key='.$customer->secure_key
        );
	}
}
