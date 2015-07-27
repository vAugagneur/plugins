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


class CashwayNotificationModuleFrontController extends ModuleFrontController
{
	public function postProcess()
	{
		switch (Tools::getValue('event'))
		{
			case 'conversion_expired':
				$order = new Order((int)Tools::getValue('order_id'));
				if (!Validate::isLoadedObject($order))
					break;

				$customer = new Customer($order->id_customer);
				$reorder_url = $this->context->link->getPageLink('order',
					true,
					$this->context->language->id,
					array('submitReorder' => '1',
						'id_order' => (int)$order->id));

				Mail::Send($this->context->language->id,
					'conversion_expired',
					Mail::l('', $this->context->language->id),
					array('{reorder_url}' => $reorder_url),
					$customer->email,
					null,
					null,
					null,
					null,
					null,
					dirname(__FILE__).'/mails/',
					false,
					$this->context->shop->id);
				break;

			default:
				# code...
				break;
		}
	}
}