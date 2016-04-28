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

if (!defined('_PS_VERSION_')) {
    exit;
}

require dirname(__FILE__).'/lib/cashway/cashway_lib.php';
require dirname(__FILE__).'/lib/cashway/compat.php';

class CashWay extends PaymentModule
{
    const VERSION = '1.2.1';

    public function __construct()
    {
        $this->name             = 'cashway';
        $this->tab              = 'payments_gateways';
        $this->version          = '1.2.1';

        $this->author           = 'CashWay';
        $this->need_instance    = 1;
        $this->bootstrap        = true;
        $this->currencies       = true;
        $this->currencies_mode  = 'checkbox';
        $this->controllers      = array('payment', 'validation', 'notification');
        $this->is_eu_compatible = 1;
        $this->module_key       = 'ca05dafd3cc2f9f98a086d70fe28c098';

        $this->ps_versions_compliancy = array('min' => '1.5.6', 'max' => _PS_VERSION_);

        parent::__construct();

        $this->displayName = $this->l('CashWay');
        $this->description = $this->l('Now your customers can pay their orders with cash.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete these details?');

        $this->limited_countries = array('FR');
        $this->limited_currencies = array('EUR');

        if (!Configuration::get('CASHWAY_API_KEY')) {
            $this->warning = $this->l('Missing API Key.');
        } else {
            $this->cashway_api_key = Configuration::get('CASHWAY_API_KEY');
        }

        if (!Configuration::get('CASHWAY_API_SECRET')) {
            $this->warning .= $this->l('Missing API Secret.');
        } else {
            $this->cashway_api_secret = Configuration::get('CASHWAY_API_SECRET');
        }

        if (false === function_exists('curl_init')) {
            $this->warning = $this->l('To be able to use this module, please activate cURL (PHP extension).');
        }
    }

    public function install()
    {
        if (false === function_exists('curl_init')) {
            $this->_errors[] =
                $this->l('This module requires the cURL PHP extension to work, it is not enabled on your server.')
                .' '
                .$this->l('Please ask your web hosting provider for assistance.');

            return false;
        }

        $iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));
        if (in_array($iso_code, $this->limited_countries) == false) {
            $this->_errors[] = $this->l('This module is not available in your country');
            return false;
        }

        return (parent::install() &&
                $this->registerHook('displayPayment') &&
                $this->registerHook('displayPaymentReturn') &&
                $this->registerHook('actionOrderStatusUpdate') &&
                $this->installDefaultValues() &&
                $this->installOrderState());
    }

    public function installDefaultValues()
    {
        Configuration::updateValue('CASHWAY_SHARED_SECRET', $this->getSharedSecret());
        Configuration::updateValue('CASHWAY_OS_PAYMENT', (int)Configuration::get('PS_OS_WS_PAYMENT'));

        return true;
    }

    private function getSharedSecret()
    {
        if (isset($_SERVER['CASHWAY_TEST_ENVIRONMENT']) &&
            isset($_SERVER['TEST_SHARED_SECRET'])) {
            return $_SERVER['TEST_SHARED_SECRET'];
        } else {
            return bin2hex(openssl_random_pseudo_bytes(24));
        }
    }

    /**
     * Register a specific order status for CashWay
    */
    public function installOrderState()
    {
        if ($order_state_id = Configuration::get('PS_OS_CASHWAY')) {
            $this->setOrderStateIcon($order_state_id);
            return true;
        }

        $order_state = new OrderState();
        $order_state->name = array();

        foreach (Language::getLanguages() as $language) {
            $order_state->name[$language['id_lang']] = 'En attente de paiement via CashWay';
        }

        $order_state->module_name = 'cashway';
        $order_state->send_email  = false;
        $order_state->color       = 'RoyalBlue';
        $order_state->invoice     = false;
        $order_state->unremovable = false;
        $order_state->hidden      = false;
        $order_state->logable     = false;
        $order_state->delivery    = false;
        $order_state->shipped     = false;
        $order_state->paid        = false;
        $order_state->deleted     = false;

        if ($order_state->add()) {
            Configuration::updateValue('PS_OS_CASHWAY', $order_state->id);
            $this->setOrderStateIcon($order_state->id);

            return true;
        }

        return false;
    }

    private function setOrderStateIcon($order_state_id)
    {
        $res = copy(
            implode(DIRECTORY_SEPARATOR, array(dirname(__FILE__), 'logo.gif')),
            implode(DIRECTORY_SEPARATOR, array(_PS_ROOT_DIR_,     'img', 'os', $order_state_id.'.gif'))
        );

        if (!$res) {
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
        if (Tools::isSubmit('submitApiKey')) {
            $key    = (string)Tools::getValue('CASHWAY_API_KEY');
            $secret = (string)Tools::getValue('CASHWAY_API_SECRET');

            if (!$key || empty($key) || !Validate::isGenericName($key)) {
                $output .= $this->displayError($this->l('Missing API key.'));
            } else {
                Configuration::updateValue('CASHWAY_API_KEY', $key);
                $output .= $this->displayConfirmation($this->l('API key updated.'));
            }

            if (!$secret || empty($secret) || !Validate::isGenericName($secret)) {
                $output .= $this->displayError($this->l('Missing API secret.'));
            } else {
                Configuration::updateValue('CASHWAY_API_SECRET', $secret);
                $output .= $this->displayConfirmation($this->l('API secret updated.'));
            }

            $this->updateNotificationParameters();
        }

        if (Tools::isSubmit('submitSettings')) {
            Configuration::updateValue('CASHWAY_OS_PAYMENT', (int)Tools::getValue('CASHWAY_OS_PAYMENT'));
            Configuration::updateValue('CASHWAY_PAYMENT_TEMPLATE', Tools::getValue('CASHWAY_PAYMENT_TEMPLATE'));
            Configuration::updateValue('CASHWAY_SEND_EMAIL', Tools::getValue('CASHWAY_SEND_EMAIL'));
            Configuration::updateValue('CASHWAY_USE_STAGING', Tools::getValue('CASHWAY_USE_STAGING'));
        }

        return $output.$this->renderForm();
    }

    public function renderForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $cashway_register_url = 'https://www.cashway.fr';
        $is_configured = self::isConfiguredService();

        $fields_form_api = array(
        array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('API Authentication'),
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
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitApiKey',
                ),
                'description' => $is_configured
                    ? ''
                    : '<p>'.sprintf(
                        $this->l('Please get your CashWay API credentials by registering below
                                 or contact us to activate your account on %s.'),
                        sprintf('<a href="%s" target="blank">www.cashway.fr</a>', $cashway_register_url)
                    ).'</p>',
                'warning' =>
                    $is_configured
                        ? $this->l('Please keep these safe in a private location;
                                   do not share them; do not send them to anyone.')
                        : ''
            )
        ));

        $ps_os_options = array();
        foreach (array('PS_OS_WS_PAYMENT', 'PS_OS_PAYMENT') as $psos) {
            $orderstate = new OrderState((int)Configuration::get($psos));
            $ps_os_options[] = array(
                'key' => (int)Configuration::get($psos),
                'name' => $orderstate->name[$this->context->language->id].' ('.$psos.')'
            );
        }

        $fields_form_settings = array(
        array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cog'
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Payment template'),
                        'name' => 'CASHWAY_PAYMENT_TEMPLATE',
                        'desc' => $this->l('Choose between a light CashWay payment button or
                                           a normal CashWay orange button.'),
                        'required' => true,
                        'options' => array(
                            'query' => array(
                                array('key' => 'light', 'name' => $this->l('Light template')),
                                array('key' => 'normal', 'name' => $this->l('CashWay Stand Out Template')),
                            ),
                            'name' => 'name',
                            'id' => 'key'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Failed payment recovery'),
                        'desc' => $this->l('Try to recover a failed payment from another payment provider.')
                            .'<br>'
                            .$this->l('This will send a recovery email from your shop to your customer,
                                      about 2 minutes after the other method failed.')
                            .'<br>'
                            .sprintf(
                                $this->l('Feel free to %sask us%s if you would like
                                         to know more about how this works.'),
                                '<a href="https://www.cashway.fr/contact/">',
                                '</a>'
                            ),
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
                        'type' => 'select',
                        'label' => $this->l('Paid Order Status'),
                        'name' => 'CASHWAY_OS_PAYMENT',
                        'desc' => $this->l('Define specific paid status effectively applied to CashWay-paid orders.')
                            .'<br>'
                            .$this->l('Note: changing this will not retroactively apply to past paid orders.'),
                        'required' => true,
                        'options' => array(
                            'query' => $ps_os_options,
                            'name' => 'name',
                            'id' => 'key'
                        )
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'name' => 'submitSettings',
                )
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

        $output = $helper->generateForm($fields_form_api);
        $output .= $helper->generateForm($fields_form_settings);

        return $output;
    }

    protected function getFormFieldsValue()
    {
        $ret = array();

        $configKeys = array(
            'CASHWAY_API_KEY',
            'CASHWAY_API_SECRET',
            'CASHWAY_PAYMENT_TEMPLATE',
            'CASHWAY_SEND_EMAIL',
            'CASHWAY_OS_PAYMENT'
        );
        foreach ($configKeys as $key) {
            $ret[$key] = Tools::getValue($key, Configuration::get($key));
        }

        return $ret;
    }

    public function hookDisplayPayment($params)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        if (!self::isConfiguredService()) {
            return;
        }

        $template = Configuration::get('CASHWAY_PAYMENT_TEMPLATE');

        if (!$template || !in_array($template, array('light', 'normal'))) {
            $template = 'light';
        }

        $this->context->smarty->assign(array(
            'template_type'        => $template,
            'cart_fee'             => self::formatFee(\CashWay\Fee::getCartFee($params['cart']->getOrderTotal())),
            'this_path'            => $this->_path,
            'this_path_cashway'    => $this->_path,
            'this_path_ssl'        => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/',
            'cashway_api_base_url' => self::getCashWayAPI()->api_base_url
        ));
        $this->context->controller->addCSS($this->_path.'views/css/cashway.css', 'all');

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
        if (!$this->active) {
            return;
        }

        if (!self::isConfiguredService()) {
            return;
        }

        $status = 'ok';
        $barcode = Tools::getValue('cw_barcode');
        $reference = isset($params['objOrder']->reference) ? $params['objOrder']->reference : '';
        $cw_res = array();

        // maybe -failed- or something valid
        if ($barcode != '-failed-') {
            $cashway = self::getCashWayAPI();
            $cw_res = $cashway->confirmTransaction($barcode, $reference, null, null);

            // TODO: log or report this.
            if (array_key_exists('errors', $cw_res)) {
                $status = 'failed';
            }
        } else {
            $status = 'failed';
        }

        $this->smarty->assign($this->paymentReturnVars(
            $barcode,
            $reference,
            $status,
            $cw_res,
            $params,
            new Address($this->context->cart->id_address_delivery)
        ));

        return $this->display(__FILE__, 'payment_return.tpl');
    }

    /**
     * @param  string   $barcode
     * @param  string   $reference
     * @param  string   $status
     * @param  string   $expires
     * @param  string   $conditions
     * @param  array    $params
     * @param  Address  $address
     *
     * @return array
    */
    private function paymentReturnVars($barcode, $reference, $status, $cw_res, $params, $address)
    {
        $expires = array_key_exists('expires_at', $cw_res) ? $cw_res['expires_at'] : null;

        $location = array(
            'address'  => $address->address1,
            'postcode' => $address->postcode,
            'city'     => $address->city,
            'country'  => $address->country
        );
        $location['search'] = implode(' ', $location);

        $expires = array_key_exists('expires_at', $cw_res) ? $cw_res['expires_at'] : null;
        $payment = array_key_exists('customer_payment', $cw_res) ? $cw_res['customer_payment'] : null;

        return array(
            'barcode'   => $barcode,
            'reference' => $reference,
            'id_order'  => $params['objOrder']->id,
            'status'    => $status,

            'total_to_pay' => Tools::displayPrice(
                $params['total_to_pay'],
                $params['currencyObj'],
                false
            ),
            'cart_fee'       => '+ '.self::formatFee(\CashWay\Fee::getCartFee($params['total_to_pay'])),
            'payment_raw'    => $payment,
            'payment'        => Tools::displayPrice($payment, $params['currencyObj'], false),
            'expires'        => $expires,
            'expires_fr'     => \CashWay\getLocalizedDateInfo($expires, 'fr'),
            'kyc_conditions' => array_key_exists('conditions', $cw_res) ? $cw_res['conditions'] : null,
            'location'       => $location,
            'cashway_api_base_url' => \CashWay\API_URL,
            'kyc_upload_url'    => \CashWay\API_URL.\CashWay\KYC_PATH,
            'kyc_upload_mail'   => \CashWay\KYC_MAIL
        );
    }

    /**
    */
    public static function formatFee($fee_value)
    {
        return sprintf('%s €', number_format($fee_value, 0, ',', '&nbsp;'));
    }

    /**
     * Notify API of a failed payment, if service is configured.
     *
     * @param array $params
     *
     * @return Array
    */
    public function hookActionOrderStatusUpdate($params)
    {
        $new_order_status = $params['newOrderStatus'];
        if ($new_order_status->id == Configuration::get('PS_OS_ERROR')) {
            if (self::isConfiguredService()) {
                $order = new Order((int)$params['id_order']);
                if (!Validate::isLoadedObject($order)) {
                    return null;
                }

                $customer = new Customer((int)$order->id_customer);
                if (!Validate::isLoadedObject($customer)) {
                    return null;
                }

                $cashway = self::getCashWayAPI();
                return $cashway->reportFailedPayment(
                    $order->id,
                    0,
                    $customer->id,
                    $customer->email,
                    $order->payment,
                    ''
                );
            }
        }

        return null;
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
            'USER_AGENT'  => 'CashWayModule/'.self::VERSION.' PrestaShop/'._PS_VERSION_,
            'USE_STAGING' => Configuration::get('CASHWAY_USE_STAGING'),
        );

        if (self::isConfiguredService()) {
            $options['API_KEY']    = Configuration::get('CASHWAY_API_KEY');
            $options['API_SECRET'] = Configuration::get('CASHWAY_API_SECRET');
        }

        if (isset($_SERVER['CASHWAY_TEST_ENVIRONMENT'])
            && $_SERVER['CASHWAY_TEST_ENVIRONMENT'] == 1) {
            $options['API_URL'] = $_SERVER['TEST_SERVER_SCHEME'].'://'.
                $_SERVER['TEST_SERVER_HOST'].':'.
                $_SERVER['TEST_SERVER_PORT'];
        }

        return new \Cashway\API($options);
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency((int)$cart->id_currency);
        $currencies_module = $this->getCurrency((int)$cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * If we have local orders pending for payment from CashWay,
     * ask CW API for recent transactions statuses, compare and act upon it.
     *
     * This method is to be called by a cron task or by the notification
     * front controller, itself triggered by a remote API call.
     *
     * @return boolean
    */
    public static function checkForPayments()
    {
        if (!self::isConfiguredService()) {
            return false;
        }

        \CashWay\Log::info('== Starting CashWay background check for orders updates ==');
        $open_orders = self::getLocalPendingOrders();
        if (count($open_orders) == 0) {
            \CashWay\Log::info('No order payment pending by CashWay.');
            return false;
        }

        $cw_orders = self::getRemoteOrderStatus();
        if (false === $cw_orders) {
            \CashWay\Log::info('No order info from remote service.');
            return false;
        }

        return self::reviewKnownOrders($open_orders, $cw_orders);
    }

    /**
     * @param Array $open_orders list of pending orders in this Shop
     * @param Array $cw_orders list of orders known to CashWay API
     *
     * @return Array hash of order ids (key) with their review status (ok/not)
    */
    public static function reviewKnownOrders($open_orders, $cw_orders)
    {
        $cw_refs   = array_keys($cw_orders);
        $open_refs = array_keys($open_orders);

        $common_refs  = array_intersect($open_refs, $cw_refs);
        $missing_refs = array_diff($open_refs, $cw_refs);

        if (count($missing_refs) > 0) {
            \CashWay\Log::warn(sprintf(
                'Some orders should be in CashWay DB but are not: %s.',
                implode(', ', $missing_refs)
            ));
        }

        $results = array();
        foreach ($common_refs as $ref) {
            $results[$ref] = self::reviewOrder($ref, $cw_orders[$ref], $open_orders[$ref]);
        }
        \CashWay\Log::info('All done.');

        return $results;
    }

    /**
     * @param string $ref
     * @param Array  $remote remote CashWay API order info
     * @param Array  $local  local PrestaShop order info
     *
     * @return boolean
    */
    public static function reviewOrder($ref, $remote, $local)
    {
        switch ($remote['status']) {
            case 'paid':
                \CashWay\Log::info(sprintf('I, found order %s has been paid. Updating local record.', $ref));
                return self::verifyAndSetPaid($ref, $remote, $local);
            case 'expired':
                \CashWay\Log::info(sprintf('I, found order %s has expired. Updating local record.', $ref));
                return self::setOrderAs((int)Configuration::get('PS_OS_CANCELED'), $local['id_order']);
            default:
            case 'confirmed':
            case 'open':
            case 'blocked':
                \CashWay\Log::info(sprintf('I, found order %s, still pending (%s).', $ref, $remote['status']));
                break;
        }
        return true;
    }

    /**
     * @param string $ref
     * @param Array  $remote
     * @param Array  $local
     *
     * @return boolean
    */
    public static function verifyAndSetPaid($ref, $remote, $local)
    {
        // TODO: if order is already paid, or expired, ignore.

        if ($local['total_paid'] != $remote['order_total']) {
            \CashWay\Log::error(sprintf(
                'expected payments differ for %s: %.2f vs. %.2f (remote/local)',
                $ref,
                $remote['order_total'],
                $local['total_paid']
            ));
            return false;
        }

        if ($local['total_paid'] > $remote['paid_amount']) {
            \CashWay\Log::error(sprintf(
                'payment is less than expected for %s: %.2f instead of %.2f (remote/local)',
                $ref,
                $remote['paid_amount'],
                $local['total_paid']
            ));
            return false;
        }

        if ($local['total_paid_real'] >= $remote['order_total']) {
            \CashWay\Log::warn('I, it has already been updated:');
            \CashWay\Log::warn('I, (local) total_paid_real = '.$local['total_paid_real']);
            \CashWay\Log::warn('I, (local) current_state = '.$local['current_state']);
            \CashWay\Log::warn('I, still trying to force order status to paid: ');

            // if the total_paid_real is already set,
            // we still force the order status to paid.
            return self::setOrderAs(
                (int)Configuration::get('CASHWAY_OS_PAYMENT'),
                $local['id_order']
            );
        } else {
            return self::setOrderAs(
                (int)Configuration::get('CASHWAY_OS_PAYMENT'),
                $local['id_order'],
                $remote['order_total'],
                $remote['barcode']
            );
        }
    }

    /**
     * Set order as paid or canceled
     *
     * @param integer $state
     * @param integer $order_id
     * @param float $order_total
     * @param string $barcode
     *
     * @return void
    */
    private static function setOrderAs($state, $order_id, $order_total = null, $barcode = null)
    {
        $return = false;

        try {
            $order = new Order((int)$order_id);
            if (!is_null($order_total) && !is_null($barcode)) {
                $order->addOrderPayment($order_total, 'CashWay', $barcode);
                $order->setInvoice(true);
            }

            $history = new OrderHistory();
            $history->id_order = (int)$order->id;
            $history->changeIdOrderState((int)$state, $order);
            $history->addWithEmail(true);

            $return = true;
        } catch (Exception $e) {
            \CashWay\Log::error($e->getMessage());
        }

        return $return;
    }

    /**
     * Fetch local orders that are still pending a payment by CashWay.
     * Index those orders by the 'reference' field, which was sent to CashWay.
     *
     * @return array
    */
    public static function getLocalPendingOrders()
    {
        $sql = sprintf(
            'SELECT * FROM %sorders WHERE current_state=%d',
            _DB_PREFIX_,
            (int)Configuration::get('PS_OS_CASHWAY')
        );

        $orders = Db::getInstance()->executeS($sql);

        if (count($orders) > 0) {
            $refs = array_map(function ($el) {
                return $el['reference'];
            }, $orders);
            $orders = array_combine($refs, array_values($orders));
        }

        return $orders;
    }

    /**
     * Return a single PS order after its id_order or reference.
     *
     * @param mixed $id_or_ref
     *
     * @return array|null
    */
    public static function getLocalOrderByReference($id_or_ref)
    {
        if (is_integer($id_or_ref)) {
            $sql = sprintf('SELECT * FROM %sorders WHERE id_order=%d', _DB_PREFIX_, (int)$id_or_ref);
        } else {
            $id_or_ref = filter_var((string)$id_or_ref, FILTER_SANITIZE_STRING);
            $sql = sprintf('SELECT * FROM %sorders WHERE reference="%s"', _DB_PREFIX_, $id_or_ref);
        }

        if ($order = Db::getInstance()->getRow($sql)) {
            return $order;
        }

        return null;
    }

    /**
     * Fetch remote (CashWay-side) status for this account.
     * FIXME. This returns ALL transactions. We should limit this to those we want.
     *
     * @return array|false
    */
    public static function getRemoteOrderStatus()
    {
        if (!self::isConfiguredService()) {
            \CashWay\Log::error('Service is not configured.');
            return false;
        }

        $cashway = self::getCashWayAPI();
        $orders = $cashway->checkTransactionsForOrders(array());
        if (array_key_exists('errors', $orders)) {
            \CashWay\Log::error(sprintf('Could not access CashWay API: %s', $orders['errors'][0]['code']));
            return false;
        }

        $refs = array_map(function ($el) {
            return $el['shop_order_id'];
        }, $orders['orders']);
        $orders = array_combine($refs, array_values($orders['orders']));

        return $orders;
    }

    /**
     * Push current PS instance notification url & secret key.
     *
     * @return null
    */
    public function updateNotificationParameters()
    {
        if (self::isConfiguredService()) {
            return self::getCashWayAPI()->updateAccount(array(
                'notification_url' => $this->context->link->getModuleLink($this->name, 'notification'),
                'shared_secret' => Configuration::get('CASHWAY_SHARED_SECRET')
            ));
        }

        return null;
    }
}
