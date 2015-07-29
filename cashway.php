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
	const VERSION = '0.5.2';

	/**
	*/
	public function __construct()
	{
		$this->name             = 'cashway';
		$this->tab              = 'payments_gateways';
		// FIXME: should use self::VERSION here but https://validator.prestashop.com doesn't see to like it...
		$this->version          = '0.5.2';
		$this->author           = 'CashWay';
		$this->need_instance    = 1;
		$this->bootstrap        = true;
		//$this->module_key       = '';
		$this->currencies       = true;
		$this->currencies_mode  = 'checkbox';
		$this->controllers      = array('payment', 'validation', 'notification');
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
				$this->registerHook('displayPayment') &&
				$this->registerHook('displayPaymentReturn') &&
				$this->registerHook('actionOrderStatusUpdate') &&
				$this->installDefaultValues() &&
				$this->installOrderState());
	}

	private function installDefaultValues()
	{
		Configuration::updateValue('CASHWAY_SHARED_SECRET', bin2hex(openssl_random_pseudo_bytes(24)));

		return true;
	}

	/**
	 * Register a specific order status for CashWay
	*/
	private function installOrderState()
	{
		if (Configuration::get('PS_OS_CASHWAY'))
			return true;

		$order_state = new OrderState();
		$order_state->name = array();

		foreach (Language::getLanguages() as $language)
			$order_state->name[$language['id_lang']] = 'En attente de paiement via CashWay';

		$order_state->send_email = false;
		$order_state->color = 'RoyalBlue';
		$order_state->invoice = false;
		$order_state->unremovable = false;
		$order_state->hidden = false;
		$order_state->logable = false;
		$order_state->delivery = false;
		$order_state->shipped = false;
		$order_state->paid = false;
		$order_state->deleted = false;

		if ($order_state->add())
		{
			Configuration::updateValue('PS_OS_CASHWAY', $order_state->id);
			if (!copy(dirname(__FILE__).DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'logo.png',
						_PS_ROOT_DIR_.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'os'.DIRECTORY_SEPARATOR.$order_state->id.'.gif'))
				$this->_errors[] = $this->l('Failed to copy order state icon.');

			return true;
		}

		return false;
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

			$notification_url = $this->context->link->getModuleLink($this->name, 'notification');

			$cashway = self::getCashWayAPI();

			$cashway->updateAccount(array(
				'notification_url' => $notification_url,
				'shared_secret' => Configuration::get('CASHWAY_SHARED_SECRET')
			));

			Configuration::updateValue('CASHWAY_PAYMENT_TEMPLATE', Tools::getValue('CASHWAY_PAYMENT_TEMPLATE'));
			Configuration::updateValue('CASHWAY_SEND_EMAIL', Tools::getValue('CASHWAY_SEND_EMAIL'));
			Configuration::updateValue('CASHWAY_USE_STAGING', Tools::getValue('CASHWAY_USE_STAGING'));
		}

		if (Tools::isSubmit('submitRegister'))
		{
			$params = array();
			$params['name'] = Tools::getValue('name');
			$params['email'] = Tools::getValue('email');
			$params['password'] = Tools::getValue('password');
			$params['phone'] = Tools::getValue('phone');
			$params['country'] = Tools::getValue('country');
			$params['company'] = Tools::getValue('company');
			$params['siren'] = Tools::getValue('siren');
			$params['url'] = $this->context->shop->getBaseURL();

			if (!$params['siren'] || empty($params['siren']))
				$params['siren'] = str_pad('', 9, '0');

			if (!$params['name'] || empty($params['name']) || !Validate::isGenericName($params['name']))
				$output .= $this->displayError($this->l('Missing name.'));
			if (!$params['password'] || empty($params['password']) || !Validate::isGenericName($params['password']))
				$output .= $this->displayError($this->l('Missing password.'));
			elseif (!$params['email'] || empty($params['email']) || !Validate::isEmail($params['email']))
				$output .= $this->displayError($this->l('Missing email.'));
			elseif (!$params['phone'] || empty($params['phone']) || !Validate::isPhoneNumber($params['phone']))
				$output .= $this->displayError($this->l('Missing phone.'));
			elseif (!$params['country'] || empty($params['country']) || !Validate::isLangIsoCode($params['country']))
				$output .= $this->displayError($this->l('Missing country.'));
			elseif (!$params['company'] || empty($params['company']) || !Validate::isGenericName($params['company']))
				$output .= $this->displayError($this->l('Missing company.'));
			else
			{
				$cashway = self::getCashWayAPI();

				$res = $cashway->registerAccount($params);

				if (isset($res['errors']))
					foreach ($res['errors'] as $key => $value)
						$output .= $this->displayError($value['code'].' => '.$value['message']);

				if ($res['status'] == 'newbie')
				{
					Configuration::updateValue('CASHWAY_API_KEY', $res['api_key']);
					Configuration::updateValue('CASHWAY_API_SECRET', $res['api_secret']);
					$notification_url = $this->context->link->getModuleLink($this->name, 'notification');

					$cashway = self::getCashWayAPI();
					$cashway->updateAccount(array(
						'notification_url' => $notification_url,
						'shared_secret' => Configuration::get('CASHWAY_SHARED_SECRET')
					));

					$output .= $this->displayConfirmation($this->l('Register completed'));
				}
			}
		}

		return $output.$this->renderForm();
	}

	public function renderForm()
	{
		$default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

		$cashway_register_url = 'https://www.cashway.fr';

		$cron_url = $this->context->link->getModuleLink('cashway',
			'status',
			array('secure_key' => md5(_COOKIE_KEY_.Configuration::get('PS_SHOP_NAME'))),
			true);

		$cron_manager_url = Tools::getShopDomain(true, true).__PS_BASE_URI__.basename(_PS_ADMIN_DIR_)
			.'/'.$this->context->link->getAdminLink('AdminModules', true).'&configure=cronjobs';

		$fields_form_registration = array(
		array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Registration'),
					'icon' => 'icon-user',
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Name'),
						'name' => 'name',
						'class' =>  'fixed-width-xxl',
						// 'size' => 64,
						'required' => true,
					),
					array(
						'type' => 'text',
						'label' => $this->l('Email'),
						'name' => 'email',
						'class' =>  'fixed-width-xxl',
						'size' => 64,
						'required' => true,
					),
					array(
						'type' => 'password',
						'label' => $this->l('Password'),
						'name' => 'password',
						'required' => true,
					),
					array(
						'type' => 'text',
						'label' => $this->l('Phone'),
						'name' => 'phone',
						'class' =>  'fixed-width-xxl',
						'required' => true,
					),
					array(
						'type' => 'select',
						'label' => $this->l('Country'),
						'name' => 'country',
						'required' => true,
						'options' => array(
							'query' => Country::getCountries($this->context->language->id),
							'name' => 'name',
							'id' => 'iso_code'
						)
					),
					array(
						'type' => 'text',
						'label' => $this->l('Company'),
						'name' => 'company',
						'class' =>  'fixed-width-xxl',
						'required' => true,
					),
					array(
						'type' => 'text',
						'label' => $this->l('Siren'),
						'name' => 'siren',
						'class' =>  'fixed-width-xxl',
						// 'required' => true,
					)
				),
				'submit' => array(
					'title' => $this->l('Send'),
					'icon' => 'icon-share-square-o',
					'name' => 'submitRegister',
				)
			)
		));

		$fields_form = array(
		array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Settings'),
					'icon' => 'icon-cog'
				),
				'input' => array(
					array(
						'type' => 'text',
						'label' => $this->l('Your CashWay API Key'),
						'name' => 'CASHWAY_API_KEY',
						'class' =>  'fixed-width-xxl',
						'size' => 64,
						'required' => true,
						'placeholder' => '36ce4a3bfddd58b558c25a77481a80fb'
					),
					array(
						'type' => 'text',
						'label' => $this->l('Your CashWay API Secret'),
						'name' => 'CASHWAY_API_SECRET',
						'class' =>  'fixed-width-xxl',
						'size' => 64,
						'required' => true,
						'placeholder' => '62ba359fa6b58bea641314e7a4635cf6'
					),
					array(
						'type' => 'select',
						'label' => $this->l('Payment template'),
						'name' => 'CASHWAY_PAYMENT_TEMPLATE',
						'required' => true,
						'options' => array(
							'query' => array(
								array('key' => 'light', 'name' => $this->l('Light template')),
								array('key' => 'normal', 'name' => $this->l('Normal template')),
							),
							'name' => 'name',
							'id' => 'key'
						)
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Send email'),
						'name' => 'CASHWAY_SEND_EMAIL',
						'is_bool' => true,
						'values' => array(
									array(
										'id' => 'active_on',
										'value' => 1,
										'label' => $this->l('Enabled')
									),
									array(
										'id' => 'active_off',
										'value' => 0,
										'label' => $this->l('Disabled')
									)
								),
					),
					array(
						'type' => 'switch',
						'label' => $this->l('Use staging'),
						'name' => 'CASHWAY_USE_STAGING',
						'is_bool' => true,
						'values' => array(
									array(
										'id' => 'active_on',
										'value' => 1,
										'label' => $this->l('Enabled')
									),
									array(
										'id' => 'active_off',
										'value' => 0,
										'label' => $this->l('Disabled')
									)
								),
						),
				),
				'submit' => array(
					'title' => $this->l('Save'),
					'name' => 'submit'.$this->name,
				)
			)
		),
		array(
			'form' => array(
				'description' =>
					'<p>'.sprintf($this->l('Get your CashWay API credentials by registering on %s.'),
						sprintf('<a href="%s" target="blank">%s</a>', $cashway_register_url, $cashway_register_url))
					.'</p>'
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

		$helper->fields_value = $this->getFormFieldsValue();

		if (self::isConfiguredService())
			return $helper->generateForm($fields_form);
		else
		{
			$output = $helper->generateForm($fields_form_registration);
			$output .= $helper->generateForm($fields_form);
			return $output;
		}
	}

	protected function getFormFieldsValue()
	{
		return array(
			'CASHWAY_API_KEY' => Tools::getValue('CASHWAY_API_KEY', Configuration::get('CASHWAY_API_KEY')),
			'CASHWAY_API_SECRET' => Tools::getValue('CASHWAY_API_SECRET', Configuration::get('CASHWAY_API_SECRET')),
			'CASHWAY_PAYMENT_TEMPLATE' => Tools::getValue('CASHWAY_PAYMENT_TEMPLATE', Configuration::get('CASHWAY_PAYMENT_TEMPLATE')),
			'CASHWAY_SEND_EMAIL' => Tools::getValue('CASHWAY_SEND_EMAIL', Configuration::get('CASHWAY_SEND_EMAIL')),
			'CASHWAY_USE_STAGING' => Tools::getValue('CASHWAY_USE_STAGING', Configuration::get('CASHWAY_USE_STAGING')),
			'name' => Tools::getValue('name'),
			'email' => Tools::getValue('email'),
			'phone' => Tools::getValue('phone'),
			'country' => Tools::getValue('country'),
			'company' => Tools::getValue('company'),
			'siren' => Tools::getValue('siren'),
		);
	}

	public function hookDisplayPayment($params)
	{
		if (!$this->active)
			return;

		if (!$this->checkCurrency($params['cart']))
			return;

		if (!self::isConfiguredService())
			return;

		$template = Configuration::get('CASHWAY_PAYMENT_TEMPLATE');

		if (!$template || !in_array($template, array('light', 'normal')))
			$template = 'light';

		$this->context->smarty->assign(array(
			'template_type' => $template,
			'cart_fee' => sprintf('+ %s €',
									number_format(\CashWay\Fee::getCartFee($params['cart']->getOrderTotal()),
													0, ',', '&nbsp;')),
			'this_path' => $this->_path,
			'this_path_cashway' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));

		return $this->display(__FILE__, 'payment.tpl');
	}

	/*
	// Pourrait être utile pour intercepter un retour d'échec de paiement
	// mais pas de certitude que ce soit systématique avec cette méthode.
	public function hookDisplayOrderConfirmation($params) {
		return $this->hookDisplayPayment($params);
	}
	*/

	public function hookDisplayPaymentReturn($params)
	{
		if (!$this->active)
			return;

		if (!self::isConfiguredService())
			return;

		$status = 'ok';
		$barcode = Tools::getValue('cw_barcode');
		// maybe -failed- or something valid
		if ($barcode != '-failed-')
		{
			$cashway = self::getCashWayAPI();
			$cw_res = $cashway->confirmTransaction(Tools::getValue('cw_barcode'),
													$params['objOrder']->reference, null, null);

			// TODO: log or report this.
			if (array_key_exists('errors', $cw_res))
				$status = 'failed';
		}
		else
			$status = 'failed';

		$address  = new Address($this->context->cart->id_address_delivery);
		$location = array(
			'address'  => $address->address1,
			'postcode' => $address->postcode,
			'city'     => $address->city,
			'country'  => $address->country
		);
		$location['search'] = implode(' ', $location);

		$this->smarty->assign(array(
			// FIXME. Add cart fee here.
			'total_to_pay' => Tools::displayPrice($params['total_to_pay'],
													$params['currencyObj'],
													false),
			'cart_fee' => sprintf('+ %s €',
				number_format(\CashWay\Fee::getCartFee($params['total_to_pay']), 0, ',', '&nbsp;')),
			'expires' => $cw_res['expires_at'],
			'location' => $location,
			'cashway_api_url' => \CashWay\API_URL,
			'barcode' => $barcode,
			'status' => $status,
			'env' => \CashWay\ENV,
			'id_order' => $params['objOrder']->id,
			'this_path' => $this->getPathUri(),
			'this_path_cashway' => $this->getPathUri(),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));

		if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference))
			$this->smarty->assign('reference', $params['objOrder']->reference);

		// Nice but does not defer/async, so we inject this in the template for now
		//$this->context->controller->addJS('https://maps.cashway.fr/js/cwm.min.js');

		return $this->display(__FILE__, 'payment_return.tpl');
	}

	public function hookActionOrderStatusUpdate($params)
	{
		$new_order_status = $params['newOrderStatus'];

		$order = new Order((int)$params['id_order']);
		if (!Validate::isLoadedObject($order))
			return;

		$customer = new Customer((int)$order->id_customer);
		if (!Validate::isLoadedObject($customer))
			return;

		if ($new_order_status->id == Configuration::get('PS_OS_ERROR'))
		{
			$cashway = self::getCashWayAPI();

			$order_cashway = array(
				'id' => $order->id,
				'total' => 0,
			);

			$customer_cashway = array(
				'id' => $customer->id,
				'total' => $customer->email,
			);

			$res = $cashway->reportFailedPayment($order->id, 0, $customer->id, $customer->email, $order->payment, '');
		}
	}

	public static function isConfiguredService()
	{
		return (Configuration::get('CASHWAY_API_KEY') &&
			Configuration::get('CASHWAY_API_SECRET'));
	}

	/**
	*/
	public static function getCashWayAPI()
	{
		$options = array(
			'USER_AGENT' => 'CashWayModule/'.self::VERSION.' PrestaShop/'._PS_VERSION_,
			'USE_STAGING' => Configuration::get('CASHWAY_USE_STAGING'),
		);

		if (self::isConfiguredService()) {
			$options['API_KEY']    = Configuration::get('CASHWAY_API_KEY');
			$options['API_SECRET'] = Configuration::get('CASHWAY_API_SECRET');
		}

		return new \Cashway\API($options);
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
		if (!self::isConfiguredService())
			return;

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
		if (!self::isConfiguredService())
		{
			\CashWay\Log::error('Service is not configured.');
			return false;
		}

		$cashway = self::getCashWayAPI();
		$orders = $cashway->checkTransactionsForOrders(array());
		if (array_key_exists('errors', $orders))
		{
			\CashWay\Log::error(sprintf('Could not access CashWay API: %s', $orders['errors'][0]['code']));
			return false;
		}

		$refs = array_map(function ($el) {
			return $el['shop_order_id'];
		}, $orders['orders']);
		$orders = array_combine($refs, array_values($orders['orders']));

		return $orders;
	}
}
