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
 * @deprecated 1.5.0 This file is deprecated, use moduleFrontController instead
 */

include(dirname(__FILE__).'/../../config/config.inc.php');
Tools::displayFileAsDeprecated();

include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/cashway.php');

$context = Context::getContext();
$cart = $context->cart;
$cashway = new cashway();

if ($cart->id_customer == 0
	|| $cart->id_address_delivery == 0
	|| $cart->id_address_invoice == 0
	|| !$cashway->active)
	Tools::redirect('index.php?controller=order&step=1');

$authorized = false;
foreach (Module::getPaymentModules() as $module)
	if ($module['name'] == 'cashway')
	{
		$authorized = true;
		break;
	}
if (!$authorized)
	die($cashway->l('This payment method is not available.', 'validation'));

$customer = new Customer($cart->id_customer);

if (!Validate::isLoadedObject($customer))
	Tools::redirect('index.php?controller=order&step=1');

$currency = $context->currency;
$total = (float)$cart->getOrderTotal(true, Cart::BOTH);

$cashway->validateOrder((int)$cart->id,
						Configuration::get('PS_OS_CASHWAY'),
						$total,
						$cashway->displayName,
						null,
						array(),
						(int)$currency->id,
						false,
						$customer->secure_key);

Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id
	.'&id_module='.(int)$cashway->id
	.'&id_order='.$cashway->currentOrder
	.'&key='.$customer->secure_key);
