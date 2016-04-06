<?php
/**
 * Plugin Name: WooCommerce CashWay
 * Plugin URI:
 * Description: WooCommerce CashWay is a payment gateway for WooCommerce
 * Author: Boris Colombier
 * Author URI: https://wba.fr
 * Version: 1.0
 * License: GPLv2 or later
 * Text Domain: woocommerce-cashway
 * Domain Path: /languages/
 * WC requires at least: 2.0
 * WC tested up to: 2.3.7.
 */

require dirname(__FILE__).'/lib/cashway_lib.php';

define('WP_DEBUG', true);

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/*
 * Check if WooCommerce is active
 **/
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    /**
     * WooCommerce fallback notice.
     */
    function woocashway_woocommerce_fallback_notice()
    {
        $html = '<div class="error">';
        $html .= '<p>'.__('The WooCommerce CashWay Gateway requires the latest version of <a href="http://wordpress.org/extend/plugins/woocommerce/" target="_blank">WooCommerce</a> to work!', 'woocommerce-cashway').'</p>';
        $html .= '</div>';
        echo $html;
    }

    /*
     * Load functions.
     */
    add_action('plugins_loaded', 'woocashway_gateway_load', 0);

    function woocashway_gateway_load()
    {
        /*
         * Load textdomain.
         */
        load_plugin_textdomain('woocommerce-cashway', false, dirname(plugin_basename(__FILE__)).'/languages/');

        if (!class_exists('WC_Payment_Gateway')) {
            add_action('admin_notices', 'woocashway_woocommerce_fallback_notice');

            return;
        }

        /*
         * Add the gateway to WooCommerce.
         */
        add_filter('woocommerce_payment_gateways', 'woocashway_add_gateway');

        function woocashway_add_gateway($methods)
        {
            $methods[] = 'WC_Gateway_Cashway';

            return $methods;
        }

        add_action('init', 'check_notification_handler');

        /**
        * Checks if the url is the route for the
        * notification handler
        * TODO Try to have a custom route like /cashway/notification
        */
        function check_notification_handler()
        {
            $current_url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            if (plugins_url('notification_handler_page.php', __FILE__) === $current_url) {
                $plugin = new WC_Gateway_Cashway();
                die($plugin->handleNotifications());
            }
        }

        /**
         * CashWay Payment Gateway.
         *
         * Provides a CashWay Payment Gateway.
         *
         * @class       WC_Gateway_Cashway
         * @extends     WC_Payment_Gateway
         */
        class WC_Gateway_Cashway extends WC_Payment_Gateway
        {
            public static function getURL($key)
            {
                $urls = [
                    'api' => 'https://api.cashway.fr/',
                    'front' => 'https://app.cashway.fr/'
                ];

                if (getenv('CASHWAY_TEST_ENVIRONMENT')) {
                    $urls = [
                        'api' => getenv('CASHWAY_TEST_API_URL').'/1',
                        'front' => getenv('CASHWAY_TEST_FRONT_URL')
                    ];
                }

                return array_key_exists($key, $urls) ? $urls[$key] : null;
            }

            public static function getAPIConf($login, $password)
            {
                $conf = array(
                    'API_KEY' => $login,
                    'API_SECRET' => $password,
                    'USER_AGENT' => 'agent/0.1'
                );

                if (getenv('CASHWAY_TEST_ENVIRONMENT')) {
                    $conf = array(
                        'API_KEY' => $login,
                        'API_SECRET' => $password,
                        'USER_AGENT' => 'agent/0.1',
                        'USE_STAGING' => true,
                        'API_URL' => getenv('CASHWAY_TEST_API_URL')
                    );
                }

                return $conf;
            }

            /**
             * Constructor for the gateway.
             */
            public function __construct()
            {
                $this->id = 'woocashway';
                $this->icon = plugins_url('assets/images/cashway.png', __FILE__);
                $this->has_fields = false;
                $this->method_title = __('CashWay', 'woocommerce-cashway');

                // Load the form fields.
                $this->init_form_fields();

                // Load the settings.
                $this->init_settings();

                // Define user setting variables.
                $this->title = $this->settings['title'];
                $this->description = $this->settings['description'];
                $this->cashway_login = $this->settings['cashway_login'];
                $this->cashway_password = $this->settings['cashway_password'];

                // Actions
                add_action('woocommerce_receipt_cashway', array(&$this, 'receipt_page'));

                add_action('woocommerce_update_options_payment_gateways_'.$this->id, array(&$this, 'process_admin_options'));

                add_action('woocommerce_thankyou_woocashway', array($this, 'thankyou_page'));

                add_action('woocommerce_cart_calculate_fees', array($this, 'cashway_surcharge'));

                add_action('woocommerce_before_checkout_form', array($this, 'checkOrderAccomplished'));

            }

            /**
            * Creates a response to return to the request emitter
            *
            * @param status the status to return
            * @param message the message to return
            */
            public function response($status, $message)
            {
                http_response_code($status);
                header('Content-Type: application/json; charset=utf-8');
                return json_encode(
                    array(
                    'status'  => ($status < 400) ? 'ok' : 'error',
                    'message' => $message
                    )
                );
            }

            /**
            * Receives the notifications on a specific route and
            * which trigger treatments and returns a status and
            * a message
            *
            * @return a json containing the status and the message
            */
            public function handleNotifications()
            {
                $api_conf = $this->getAPIConf();
                $api = new \CashWay\API($api_conf);
                $res = $api->receiveNotification(
                    file_get_contents('php://input'),
                    getallheaders(),
                    getenv('NOTIFICATION_HANDLER_SHARED_KEY')
                );

                if ($res[0] === false) {
                    return $this->response($res[2], $res[1]);
                } else {
                    $event = $res[1];
                    $data = $res[2];

                    switch ($event) {
                        case 'transaction_confirmed':
                            //Update the status of the order, reduce the stock and
                            //empty the customer's cart
                            $order = wc_get_order($data->order_id);
                            $order->update_status('on-hold', __('Awaiting CashWay payment', 'woocommerce'));
                            $order->reduce_order_stock();
                            WC()->cart->empty_cart();
                            return $this->response(200, 'Ok, transaction set to confirmed.');
                            break;
                        case 'transaction_expired':
                            //Set the status of the order to cancelled
                            $order = wc_get_order($data->order_id);
                            $order->update_status('cancelled', __('CashWay transaction expired', 'woocommerce'));
                            return $this->response(200, 'Ok, transaction set to cancelled.');
                            break;
                        default:
                            return $this->response(400, 'Unknown Event.');
                            break;
                    }
                }
            }

            function cashway_surcharge()
            {
                // Ajout des frais CashWay
                global $woocommerce;
                $feeObject = new \CashWay\Fee;

                if (is_admin() && !defined('DOING_AJAX')) {
                    return;
                }
                $current_gateway = WC()->session->chosen_payment_method;
                if ($current_gateway != 'woocashway') {
                    return;
                }
                $total_amount = $woocommerce->cart->cart_contents_total + $woocommerce->cart->shipping_total;
                $fee = $feeObject->getCartFee($total_amount);
                $woocommerce->cart->add_fee(__('Custom', 'Frais CashWay'), $fee, true);
            }

            /**
             * Check If The Gateway Is Available For Use.
             *
             * @return bool
             */
            public function is_available()
            {
                if ($this->enabled == 'yes') {
                    return true;
                }
            }

            /**
             * Add specific infos on admin page.
             */
            public function admin_options()
            {
                wp_enqueue_style('wooCashWayStylesheet', plugins_url('assets/css/cashway.css', __FILE__));
                wp_enqueue_script('cashway-script', plugins_url('assets/js/cashway.js', __FILE__));
                $js_params = array(
                  'checking' => __('Checking CashWay settings...', 'woocommerce-cashway'),
                  'error_connecting' => __('An error occurred while connecting to CashWay.', 'woocommerce-cashway'),
                  'error_login' => __('Please check your CashWay API Key and Secret.', 'woocommerce-cashway'),
                  'error_unknown' => __('An error occurred, please contact us on https://www.cashway.fr/', 'woocommerce-cashway'),
                  'url' => site_url().'/?cashway=check_parameters',
                );
                wp_localize_script('cashway-script', 'CashWayJSParams', $js_params);
                ?>
                <div id="wc_get_started" class="cashway">
                    <h4><?php _e('Acceptez le paiement en liquide sur votre boutique en ligne', 'woocommerce-cashway');
                ?></h4>
                    <span><?php _e('Ajouter le paiement via CashWay sur votre boutique en quelques clics.', 'woocommerce-cashway');
                ?></span><br/>
                </div>
                <table class="form-table parameters">
                    <?php $this->generate_settings_html();
                ?>
                </table>
                <?php

            }

            /**
             * Start Gateway Settings Form Fields.
             */
            public function init_form_fields()
            {
                $this->form_fields = array(
                    'enabled' => array(
                        'title' => __('Enable/Disable', 'woocommerce-cashway'),
                        'type' => 'checkbox',
                        'label' => __('Activer CashWay', 'woocommerce-cashway'),
                        'description' => __('NB: La devise de votre boutique doit être l\'euro €.', 'woocommerce-cashway'),
                        'default' => 'no',
                    ),
                    'test_mode' => array(
                        'title' => __('Mode test', 'woocommerce-cashway'),
                        'type' => 'checkbox',
                        'label' => __('Use CashWay in test mode', 'woocommerce-cashway'),
                        'default' => 'no',
                        'description' => __('Use test mode to test transactions with no real payment required', 'woocommerce-cashway'),
                    ),
                    'title' => array(
                        'title' => __('Title', 'woocommerce-cashway'),
                        'type' => 'text',
                        'description' => __('Le titre de la méthode de paiement que voient vos visiteurs.', 'woocommerce-cashway'),
                        'default' => __('Payez en liquide avec CashWay', 'woocommerce-cashway'),
                    ),
                    'description' => array(
                        'title' => __('Description', 'woocommerce-cashway'),
                        'type' => 'textarea',
                        'description' => __('La description affichée pour vos visiteurs.', 'woocommerce-cashway'),
                        'default' => __('Payez en liquide avec CashWay.', 'woocommerce-cashway'),
                    ),
                    'cashway_login' => array(
                        'title' => __('CashWay API Key', 'woocommerce-cashway'),
                        'type' => 'text',
                        'description' => __('Votre clé d\'API CashWay.', 'woocommerce-cashway'),
                        'default' => '',
                    ),
                    'cashway_password' => array(
                        'title' => __('CashWay API Secret', 'woocommerce-cashway'),
                        'type' => 'password',
                        'description' => __('Votre secret d\'API CashWay', 'woocommerce-cashway'),
                        'default' => '',
                    ),
                    'cashway_parameters' => array(
                        'title' => '',
                        'type' => 'hidden',
                        'default' => '',
                        'description' => '',
                    ),
                );
            }

            /**
             * Process the payment and return the result.
             *
             * @param int $order_id
             *
             * @return array
             */
            public function process_payment($order_id)
            {
                $order = wc_get_order($order_id);
                $api_conf = $this->getAPIConf($this->cashway_login, $this->cashway_password);
                $api = new \CashWay\API($api_conf);

                $customer_id = $order->user_id;
                $customer_name = $order->billing_first_name.' '.$order->billing_last_name;
                $customer_email = $order->billing_email;
                $customer_phone = $order->billing_phone;
                $customer_city = $order->billing_city;
                $customer_zipcode = $order->billing_postcode;
                $customer_country = $order->billing_country;
                $customer_address = $order->billing_address_1;
                $customer_company = $order->billing_company;
                $customer = array(
                    'id' => $customer_id,
                    'name' => $customer_name,
                    'email' => $customer_email,
                    'phone' => $customer_phone,
                    'city' => $customer_city,
                    'zipcode' => $customer_zipcode,
                    'country' => $customer_country,
                    'address' => $customer_address,
                    'company' => $customer_company
                );

                $order_id = $order_id;
                $order_at = date('Y-m-d#G:i:s#');
                $order_total = $order->get_total();
                $order_currency = $order->get_order_currency();
                $order_items_count = $order->get_item_count();
                $order_details = $order->get_items();
                $order_language = 'fr';
                $order = array(
                    'at' => $order_at,
                    'total' => $order_total,
                    'items_count' => $order_items_count,
                    'details' => $order_details
                );

                $api->setOrder('woocommerce', $order_id, $order, $customer, $order_language, $order_currency);
                $response = $api->openTransaction();
                $barcode = $response['barcode'];
                $shop_order_id = $response['shop_order_id'];
                update_post_meta($order_id, 'cashway_barcode', sanitize_text_field($barcode));
                $order = wc_get_order($order_id);
                return array(
                    'result' => 'success',
                    'redirect' => $this->getURL('front').'/t/'.$shop_order_id.'?return_url='.$this->get_return_url($order)
                );
            }

            /**
             * Output for the order received page.
             */
            public function receipt_page($order)
            {
                echo $this->generate_cashway_form($order);
            }

            /**
            * Check if an order has been confirmed, if it's the case,
            * display the thank you page
            */
            public function checkOrderAccomplished()
            {
                if (null != $_GET['shop_order_id']) {
                    $this->thankyou_page($_GET['shop_order_id']);
                }
            }

            /**
             * Output for the order received page.
             */
            public function thankyou_page($order_id)
            {
                if (null != $order_id) {
                    $order = wc_get_order($order_id);
                    $barcode = get_post_meta($order_id, 'cashway_barcode', true);
                    $api_conf = $this->getAPIConf($this->cashway_login, $this->cashway_password);

                    echo "
                      <h1>Merci d'avoir commandé avec CashWay !</h1>
                      <h2>Récapitulatif de votre commande :</h2>
                      Code barre : ".$barcode."<br />
                      Montant de la commande : ".$order->get_total()."<br />
                      Moyen de paiement : CashWay<br />
                    ";
                    die();
                } else {
                    die('Echec du traitement de votre commande...');
                }
            }
        }
    }
    function cashway_parse_request($wp)
    {
        if (array_key_exists('cashway', $wp->query_vars) && ($wp->query_vars['cashway'] == 'check_parameters')) {
            $headers = array(
               'Authorization' => 'Basic '.base64_encode($_POST['login'].':'.$_POST['password']),
            );
            // Setup variable for wp_remote_get
            $args = array(
               'headers' => $headers,
            );
            $response = wp_remote_get('https://api-staging.cashway.fr/1/shops/me/status', $args);
            $code = $response['response']['code'];
            if ($code == 200) {
                echo 'ok';
            } else {
                die('errorConnection');
            }
            die();
        }
    }
    add_action('parse_request', 'cashway_parse_request');

    function cashway_query_vars($vars)
    {
        $vars[] = 'cashway';

        return $vars;
    }
    add_filter('query_vars', 'cashway_query_vars');
}
