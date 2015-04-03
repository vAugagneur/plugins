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

if (!defined('_PS_VERSION_'))
	exit;

require dirname(__FILE__).'/lib/cashway/cashway_lib.php';

class CashWay extends PaymentModule
{
	const VERSION = '0.1.1';

	/**
	*/
	public function __construct()
	{
		$this->name             = 'cashway';
		$this->tab              = 'payments_gateways';
		// FIXME: should use self::VERSION here but https://validator.prestashop.com doesn't see to like it...
		$this->version          = '0.1.1';
		$this->author           = 'CashWay';
		$this->need_instance    = 1;
		$this->bootstrap        = true;
		//$this->module_key       = '';
		$this->currencies       = true;
		$this->currencies_mode  = 'checkbox';
		$this->controllers      = array('payment', 'validation');
		$this->is_eu_compatible = 1;

		$this->ps_versions_compliancy = array('min' => '1.5');

		parent::__construct();

		$this->displayName = $this->l('CashWay');
		$this->description = $this->l('Now your customers can pay their orders with cash.');
		$this->confirmUninstall = $this->l('Are you sure you want to delete these details?');

		if (!Configuration::get('CASHWAY_API_KEY'))
			$this->warning = $this->l('Missing API Key.');
		else
			$this->cashway_api_key = Configuration::get('CASHWAY_API_KEY');

		if (!Configuration::get('CASHWAY_API_SECRET'))
			$this->warning .= $this->l('Missing API Secret.');
		else
			$this->cashway_api_secret = Configuration::get('CASHWAY_API_SECRET');

		if (false === function_exists('curl_init'))
			$this->warning = $this->l('To be able to use this module, please activate cURL (PHP extension).');
	}

	public function install()
	{
		if (false === function_exists('curl_init'))
		{
			$this->_errors[] =
				$this->l('This module requires the cURL PHP extension to work, it is not enabled on your server.')
				.' '
				.$this->l('Please ask your web hosting provider for assistance.');

			return false;
		}

		return (parent::install() &&
				$this->registerHook('payment') &&
				$this->registerHook('paymentReturn') &&
				$this->installOrderState());
	}

	/**
	 * Register a specific order status for CashWay
	*/
	private function installOrderState()
	{
		if (Configuration::get('PS_OS_CASHWAY'))
			return true;

		$values_to_insert = array(
			'invoice' => 1,
			'send_email' => 0,
			'module_name' => $this->name,
			'color' => 'RoyalBlue',
			'unremovable' => 0,
			'hidden' => 0,
			'logable' => 0,
			'delivery' => 0,
			'shipped' => 0,
			'paid' => 0,
			'deleted' => 0
		);

		if (!Db::getInstance()->autoExecute(_DB_PREFIX_.'order_state', $values_to_insert, 'INSERT'))
		{
			$this->_errors[] = $this->l('Failed to register a new order state.');
			return false;
		}

		$id_order_state = (int)Db::getInstance()->Insert_ID();
		Configuration::updateValue('PS_OS_CASHWAY', $id_order_state);

		$languages = Language::getLanguages(false);
		foreach ($languages as $language)
			Db::getInstance()->autoExecute(_DB_PREFIX_.'order_state_lang', array(
				'id_order_state' => $id_order_state,
				'id_lang' => $language['id_lang'],
				'name' => 'En attente de paiement via CashWay',
				'template' => ''
			), 'INSERT');

		if (!copy(dirname(__FILE__).DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'logo.png',
					_PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'os'.DIRECTORY_SEPARATOR.$id_order_state.'.gif'))
		{
			$this->_errors[] = $this->l('Failed to copy order state icon.');
			return false;
		}

		return true;
	}

	public function uninstall()
	{
		Configuration::deleteByName('CASHWAY_API_KEY');
		Configuration::deleteByName('CASHWAY_API_SECRET');

		// DO NOT uninstall database. Keep history of events.

		return parent::uninstall();
	}

	public function getContent()
	{
		$output = null;

		if (Tools::isSubmit('submit'.$this->name))
		{
			$key    = (string)Tools::getValue('CASHWAY_API_KEY');
			$secret = (string)Tools::getValue('CASHWAY_API_SECRET');

			if (!$key || empty($key) || !Validate::isGenericName($key))
				$output .= $this->displayError($this->l('Missing API key.'));
			else
			{
				Configuration::updateValue('CASHWAY_API_KEY', $key);
				$output .= $this->displayConfirmation($this->l('API key updated.'));
			}

			if (!$secret || empty($secret) || !Validate::isGenericName($secret))
				$output .= $this->displayError($this->l('Missing API secret.'));
			else
			{
				Configuration::updateValue('CASHWAY_API_SECRET', $secret);
				$output .= $this->displayConfirmation($this->l('API secret updated.'));
			}
		}

		return $output.$this->renderForm();
	}

	public function renderForm()
	{
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		$cashway_register_url = 'https://www.cashway.fr';

		$cron_url = Tools::getShopDomain(true, true).__PS_BASE_URI__.basename(_PS_ADMIN_DIR_)
			.'/cron_cashway_check_for_transactions.php?secure_key='.md5(_COOKIE_KEY_.Configuration::get('PS_SHOP_NAME'));

		$cron_manager_url = Tools::getShopDomain(true, true).__PS_BASE_URI__.basename(_PS_ADMIN_DIR_)
			.'/index.php?controller=AdminModules&token='.Tools::getAdminTokenLite('AdminModules')
			.'&configure=cronjobs&tab_module=administration&module_name=cronjobs';

		$fields_form = array(
		array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings')
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Your CashWay API Key'),
						'name' => 'CASHWAY_API_KEY',
						'size' => 64,
						'required' => true,
						'placeholder' => '36ce4a3bfddd58b558c25a77481a80fb'
					),
					array(
						'type' => 'text',
						'label' => $this->l('Your CashWay API Secret'),
						'name' => 'CASHWAY_API_SECRET',
						'size' => 64,
						'required' => true,
						'placeholder' => '62ba359fa6b58bea641314e7a4635cf6'
					)
				),
				'submit' => array(
					'title' => $this->l('Save')
				)
			)
		),
		array(
			'form' => array(
				'description' =>
					'<p>'.sprintf($this->l('Get your CashWay API credentials by registering on %s.'),
						sprintf('<a href="%s" target="blank">%s</a>', $cashway_register_url, $cashway_register_url))
					.'</p><p>'
						.sprintf($this->l('Please make sure a Cron task calls this address about every 2 hours to update order payment status:')
						.sprintf('<br><textarea onclick="this.focus();this.select();" readonly>%s</textarea>', $cron_url))
						.'</p><p>'.sprintf($this->l('You may do so using PrestaShop %sCron Task Manager%s.'),
								'<a href="'.$cron_manager_url.'">', '</a>').'</p>'
			)
		));

		$helper = new HelperForm();

		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

		$helper->default_form_language = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;

		$helper->title = $this->displayName;
		$helper->show_toolbar = true;
		$helper->toolbar_scroll = true;
		$helper->submit_action = 'submit'.$this->name;
		$helper->toolbar_btn = array(
			'save' =>
			array(
				'desc' => $this->l('Save'),
				'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
				'&token='.Tools::getAdminTokenLite('AdminModules'),
			),
			'back' => array(
				'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
				'desc' => $this->l('Back to list')
			)
		);

		$helper->fields_value['CASHWAY_API_KEY']    = Configuration::get('CASHWAY_API_KEY');
		$helper->fields_value['CASHWAY_API_SECRET'] = Configuration::get('CASHWAY_API_SECRET');

		return $helper->generateForm($fields_form);
	}

	public function hookPayment($params)
	{
		if (!$this->active)
			return;

		if (!$this->checkCurrency($params['cart']))
			return;

		$this->smarty->assign(array(
			'cart_fee' => sprintf('+ %s €',
									number_format(\CashWay\Fee::getCartFee($params['cart']->getOrderTotal()),
													0, ',', '&nbsp;')),
			'this_path' => $this->_path,
			'this_path_cashway' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
		return $this->display(__FILE__, 'payment.tpl');
	}

	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return;

		$cashway = self::getCashWayAPI();
		$cw_res = $cashway->confirmTransaction(Tools::getValue('cw_barcode'),
												$params['objOrder']->reference, null, null);

		// TODO: check $cw_res error cases

		$state = $params['objOrder']->getCurrentState();
		if (in_array($state, array(Configuration::get('PS_OS_CASHWAY'),
									Configuration::get('PS_OS_OUTOFSTOCK'),
									Configuration::get('PS_OS_OUTOFSTOCK_UNPAID'))))
		{
			$address  = new Address($this->context->cart->id_address_delivery);
			$location = array(
				'address' => $address->address1,
				'postcode' => $address->postcode,
				'city' => $address->city,
				'country' => $address->country
			);
			$location['search'] = implode(' ', $location);

			$this->smarty->assign(array(
				// FIXME. Add cart fee here.
				'total_to_pay' => Tools::displayPrice($params['total_to_pay'],
														$params['currencyObj'],
														false),
				'expires' => $cw_res['expires_at'],
				'location' => $location,
				'cashway_api_url' => \CashWay\API_URL,
				'barcode' => Tools::getValue('cw_barcode'),
				'status' => 'ok',
				'env' => \CashWay\ENV,
				'id_order' => $params['objOrder']->id,
				'this_path' => $this->getPathUri(),
				'this_path_cashway' => $this->getPathUri(),
				'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
			));

			if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference))
				$this->smarty->assign('reference', $params['objOrder']->reference);

		}
		else
			$this->smarty->assign('status', 'failed');

		return $this->display(__FILE__, 'payment_return.tpl');
	}

	/**
	*/
	public static function getCashWayAPI()
	{
		if (Configuration::get('CASHWAY_API_KEY') && Configuration::get('CASHWAY_API_SECRET'))
			return new \Cashway\API(array(
				'API_KEY' => Configuration::get('CASHWAY_API_KEY'),
				'API_SECRET' => Configuration::get('CASHWAY_API_SECRET'),
				'USER_AGENT' => 'CashWayModule/'.self::VERSION.' PrestaShop/'._PS_VERSION_
			));

		return null;
	}

	public function checkCurrency($cart)
	{
		$currency_order = new Currency((int)$cart->id_currency);
		$currencies_module = $this->getCurrency((int)$cart->id_currency);

		if (is_array($currencies_module))
		{
			foreach ($currencies_module as $currency_module)
			{
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
			}
		}

		return false;
	}

	/**
	 * If we have local orders pending for payment from CashWay,
	 * ask CW API for recent transactions statuses, compare and act upon it.
	 *
	 * This method is expected to be called by a cron task at least every hour.
	 * See cron_cashway_check_for_transactions.php
	 *
	 * @return boolean
	*/
	public static function checkForPayments()
	{
		\CashWay\Log::info('== Starting CashWay background check for orders updates ==');
		$open_orders = self::getLocalPendingOrders();
		if (count($open_orders) == 0)
		{
			\CashWay\Log::info('No order payment pending by CashWay.');
			return true;
		}

		$cw_orders = self::getRemoteOrderStatus();
		if (false === $cw_orders)
			return false;

		$cw_refs = array_keys($cw_orders);
		$open_refs = array_keys($open_orders);

		$common_refs = array_intersect($open_refs, $cw_refs);
		$missing_refs = array_diff($open_refs, $cw_refs);

		if (count($missing_refs) > 0)
			\CashWay\Log::warn(sprintf('Some orders should be in CashWay DB but are not: %s.',
				implode(', ', $missing_refs)));

		foreach ($common_refs as $ref)
		{
			switch ($cw_orders[$ref]['status'])
			{
				case 'paid':
					\CashWay\Log::info(sprintf('I, found order %s was paid. Updating local record.', $ref));
					if ($cw_orders[$ref]['paid_amount'] != $open_orders[$ref]['total_paid'])
						\CashWay\Log::warn(sprintf('W, Found order %s but paid amount does not match: is %.2f but should be %.2f.',
							$ref,
							$cw_orders[$ref]['paid_amount'],
							$open_orders[$ref]['total_paid']));

					if ($open_orders[$ref]['total_paid_real'] >= $cw_orders[$ref]['order_total'])
						\CashWay\Log::warn('Well, it looks like it has already been updated: skipping this step.');
					else
					{
						$order = new Order($open_orders[$ref]['id_order']);
						$order->addOrderPayment($cw_orders[$ref]['paid_amount'],
							'CashWay',
							$cw_orders[$ref]['barcode']);
						$order->setInvoice(true);

						$history = new OrderHistory();
						$history->id_order = $order->id;
						$history->changeIdOrderState((int)Configuration::get('PS_OS_WS_PAYMENT'), $order, !$order->hasInvoice());
					}
					break;

				case 'expired':
					\CashWay\Log::info(sprintf('I, found order %s expired. Updating local record.', $ref));
					$order = new Order($open_orders[$ref]['id_order']);
					$history = new OrderHistory();
					$history->id_order = $order->id;
					$history->changeIdOrderState((int)Configuration::get('PS_OS_CANCELED'), $order, !$order->hasInvoice());
					break;

				default:
				case 'confirmed':
				case 'open':
					\CashWay\Log::info(sprintf('I, found order %s, still pending.', $ref));
					break;
			}
		}
		return true;
	}

	/**
	 * Fetch local orders that are still pending a payment by CashWay.
	 * Index those orders by the 'reference' field, which was sent to CashWay.
	 *
	 * @return array
	*/
	public static function getLocalPendingOrders()
	{
		$sql = sprintf('SELECT * FROM %sorders WHERE current_state=%d',
						_DB_PREFIX_,
						(int)Configuration::get('PS_OS_CASHWAY'));

		$orders = Db::getInstance()->executeS($sql);

		if (count($orders) > 0)
		{
			$refs = array_map(function ($el) { return $el['reference'];
			}, $orders);
			$orders = array_combine($refs, array_values($orders));
		}

		return $orders;
	}

	/**
	 * Fetch remote (CashWay-side) status for this account.
	 * FIXME. This returns ALL transactions. We should limit this to those we want.
	 *
	 * @return array|false
	*/
	public static function getRemoteOrderStatus()
	{
		$cashway = self::getCashWayAPI();
		if (is_null($cashway))
		{
			\CashWay\Log::error('Could not access CashWay API.');
			return false;
		}

		$orders = $cashway->checkTransactionsForOrders(array());
		// TODO: check for error returned, return false

		$refs = array_map(function ($el) { return $el['shop_order_id'];
		}, $orders['orders']);
		$orders = array_combine($refs, array_values($orders['orders']));

		return $orders;
	}
}