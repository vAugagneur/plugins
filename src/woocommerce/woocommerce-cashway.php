<?php
/**
 * Plugin Name: WooCommerce CashWay
 * Plugin URI:
 * Description: WooCommerce CashWay est une méthode de paiement pour WooCommerce
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
            /**
            * Returns the urls for the asked module
            * depending of the current environment (dev/production)
            *
            * @param String the asked module
            *
            * @return String the asked module's url
            */
            public static function get_url($key)
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

            /**
            * Returns a configuration for the API instance,
            * depending of the current environment (dev/production)
            *
            * @param String login the login from the plugin's settings
            * @param String password the password from the plugin's settings
            *
            * @return Array the configuration for the API
            */
            function get_api_conf($login = null, $password = null)
            {
                $login = ($login === null) ? $this->cashway_login : $login;
                $password = ($password === null) ? $this->cashway_password : $password;
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
            * Checks if the plugin has a key and a secret in it's
            * settings
            *
            * @return boolean
            */
            function is_ready_for_production()
            {
                return '' != $this->cashway_login && '' != $this->cashway_password;
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

                add_action('woocommerce_before_checkout_form', array($this, 'check_order_accomplished'));

                // Filters
                add_filter('woocommerce_available_payment_gateways', array($this, 'available_payment_gateways_master'), '1');

            }

            /**
            * Checks if the plugin has a key and a secret in it's settings,
            * if it's the case, calls the method to calculate and display the fees,
            * if it's not, deletes the CashWay payment gateway from the list passed by the hook
            *
            * @param Array the list of gateways passed by the hook
            *
            * @return Array the list of available gateways processed by the method
            */
            function available_payment_gateways_master($gateways)
            {
                if ($gateways['woocashway']) {
                    if (!$this->is_ready_for_production()) {
                        unset($gateways['woocashway']);
                    } else {
                        $this->add_fees_gateway_description($gateways);
                    }
                }
                return $gateways;
            }

            /**
            * Adds the CashWay Fees to the payment gateway's description
            *
            * @param Array the list of gateways passed by the hook
            *
            * @return Array the list of available gateways processed by the method
            */
            function add_fees_gateway_description($gateways)
            {
                if ($gateways['woocashway']) {
                    if (strpos($gateways['woocashway']->description, 'Frais')) {
                        return $gateways;
                    }
                    $gateways['woocashway']->description .= ' (Frais Supplémentaires : '.$this->cashway_surcharge().'€)';
                }
                return $gateways;
            }

            /**
            * Creates a response to return to the request emitter
            *
            * @param Integer status the status to return
            * @param String message the message to return
            *
            * @return String the json response
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
            public function handle_notifications()
            {
                $api = new \CashWay\API($this->get_api_conf());
                $res = $api->receiveNotification(
                    file_get_contents('php://input'),
                    getallheaders(),
                    get_option('notification_handler_shared_key')
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
                        case 'transaction_paid':
                            //Set the status of the order to completed
                            $order = wc_get_order($data->order_id);
                            $order->update_status('completed', __('CashWay transaction completed', 'woocommerce'));
                            return $this->response(200, 'Ok, transaction set to completed.');
                            break;
                        default:
                            return $this->response(400, 'Unknown Event.');
                            break;
                    }
                }
            }

            /**
            * Calculates and returns the CashWay fees
            *
            * @return Integer the calculated fees
            */
            function cashway_surcharge()
            {
                // Ajout des frais CashWay
                global $woocommerce;
                $feeObject = new \CashWay\Fee;
                $total_amount = $woocommerce->cart->cart_contents_total + $woocommerce->cart->shipping_total;
                return $feeObject->getCartFee($total_amount);
            }

            /**
             * Checks If The Gateway Is Available For Use.
             *
             * @return boolean
             */
            public function is_available()
            {
                if ($this->enabled == 'yes') {
                    return true;
                }
            }

            /**
             * Adds specific infos on admin page.
             */
            public function admin_options()
            {
                wp_enqueue_style('wooCashWayStylesheet', plugins_url('assets/css/cashway.css', __FILE__));
                wp_enqueue_script('cashway-script', plugins_url('assets/js/cashway.js', __FILE__));
                $js_params = array(
                  'checking' => __('Vérification des paramètres de CashWay...', 'woocommerce-cashway'),
                  'error_connecting' => __('Une erreur s\'est produite lors de la connexion à CashWay...', 'woocommerce-cashway'),
                  'error_login' => __('Veuillez vérifier votre clé et votre secret d\'API CashWay.', 'woocommerce-cashway'),
                  'error_send_infos' => __('Erreur lors de l\'envoi de l\'url de notification et/ou de la shared key.', 'woocommerce-cashway'),
                  'error_unknown' => __('Une erreur s\'est produite, vous pouvez nous contacter sur https://www.cashway.fr/', 'woocommerce-cashway'),
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
             * Starts Gateway Settings Form Fields.
             */
            public function init_form_fields()
            {
                $this->form_fields = array(
                    'enabled' => array(
                        'title' => __('Activer/Désactiver', 'woocommerce-cashway'),
                        'type' => 'checkbox',
                        'label' => __('Activer CashWay', 'woocommerce-cashway'),
                        'description' => __('NB: La devise de votre boutique doit être l\'euro €.', 'woocommerce-cashway'),
                        'default' => 'no',
                    ),
                    'test_mode' => array(
                        'title' => __('Mode Test', 'woocommerce-cashway'),
                        'type' => 'checkbox',
                        'label' => __('Utiliser CashWay en mode Test', 'woocommerce-cashway'),
                        'default' => 'no',
                        'description' => __('Utiliser le mode Test pour tester des transactions sans paiement réel requis', 'woocommerce-cashway'),
                    ),
                    'title' => array(
                        'title' => __('Titre', 'woocommerce-cashway'),
                        'type' => 'text',
                        'description' => __('Le titre de la méthode de paiement que voient vos visiteurs.', 'woocommerce-cashway'),
                        'default' => __('Payez en liquide avec CashWay.', 'woocommerce-cashway'),
                    ),
                    'description' => array(
                        'title' => __('Description', 'woocommerce-cashway'),
                        'type' => 'textarea',
                        'description' => __('La description affichée pour vos visiteurs.', 'woocommerce-cashway'),
                        'default' => __('Payez en liquide avec CashWay.', 'woocommerce-cashway'),
                    ),
                    'cashway_login' => array(
                        'title' => __('Clé d\'API CashWay', 'woocommerce-cashway'),
                        'type' => 'text',
                        'description' => __('Votre clé d\'API CashWay.', 'woocommerce-cashway'),
                        'default' => '',
                    ),
                    'cashway_password' => array(
                        'title' => __('Secret d\'API', 'woocommerce-cashway'),
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
             * Open a transaction for this order on the remote API and process the WooCommerce order :
             * - in case of success, keep a copy of the transaction id and redirect the customer to CW front Web app
             * - in case of failure, redirect the customer to an explanation page, allowing to return to checkout page for another method.
             * - in case of bad plugin authentication, display a message telling the customer that the service is unavailable
             *
             * @param String order_id the id of the order to process and open a transaction for
             *
             * @return Array the result (success/failure) and the redirection url to the CW front Web app in case of success
            */
            public function process_payment($order_id)
            {
                $order = wc_get_order($order_id);
                $api = new \CashWay\API($this->get_api_conf());

                $api->setOrder('woocommerce', $order_id, $order);
                $payment_total = $order->get_total() + $this->cashway_surcharge();
                if ($payment_total <= 1000) {
                    $response = $api->openTransaction();
                    if($response['status']) {
                        $barcode = $response['barcode'];
                        $shop_order_id = $response['shop_order_id'];
                        update_post_meta($order_id, 'cashway_barcode', sanitize_text_field($barcode));
                        $order = wc_get_order($order_id);
                        return array(
                            'result' => 'success',
                            'redirect' => $this->get_url('front').'/t/'.$shop_order_id.'?return_url='.$this->get_return_url($order)
                        );
                    } else {
                        foreach ($response['errors'] as $value) {
                            //Simple check to translate the error message on auth error
                            $error_message = ($value['status'] === '401 Not authorized') ? 'Service CashWay indisponible pour cette commande.' : $value['status'];
                            wc_add_notice($error_message, 'error');
                            return array(
                                'result' => 'failure'
                            );
                        }
                    }
                } else {
                    wc_add_notice('Votre commande dépasse la limite de montant pour le paiement avec Cashway  (<a href="https://help.cashway.fr/cgu/">Plus d\'informations</a>)', 'error');
                    return array(
                        'result' => 'failure'
                    );
                }
            }

            /**
             * Output for the order received page.
             *
             * @param Object order the order
             */
            public function receipt_page($order)
            {
                echo $this->generate_cashway_form($order);
            }

            /**
            * Check if an order has been processed, if it's the case,
            * display the thank you page
            */
            public function check_order_accomplished()
            {
                if (null != $_GET['shop_order_id']) {
                    $this->thankyou_page($_GET['shop_order_id']);
                }
            }

            /**
             * Output for the order received page.
             *
             * @param String order_id the id of the order to display the Thank
             * You Page for
             */
            public function thankyou_page($order_id)
            {
                if (null != $order_id) {
                    $order = wc_get_order($order_id);
                    $barcode = get_post_meta($order_id, 'cashway_barcode', true);

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

    /**
    * Parses the request on Cashway routes to determine which code to execute
    *
    * @param Object wp a WordPress instance
    */
    function cashway_parse_request($wp)
    {
        if (array_key_exists('cashway', $wp->query_vars)) {
            switch ($wp->query_vars['cashway']) {
                case 'check_parameters':
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
                        $plugin = new WC_Gateway_Cashway();
                        $api_conf = $plugin->get_api_conf($_POST['login'], $_POST['password']);
                        $api = new \CashWay\API($api_conf);
                        $shared_secret = bin2hex(openssl_random_pseudo_bytes(24));
                        $args = array(
                            'notification_url' => get_site_url().'/?cashway=notification',
                            'cashway_shared_secret' => $shared_secret
                        );
                        $response = $api->updateAccount($args);
                        if ($response['notification_hook_url']) {
                            update_option('notification_handler_shared_key', $shared_secret);
                            echo 'ok';
                        } else {
                            die('errorUpdateConnection');
                        }
                    } else {
                        die('errorConnection');
                    }
                    die();
                    break;
                case 'notification':
                    $plugin = new WC_Gateway_Cashway();
                    die($plugin->handle_notifications());
                    break;
                default:
                    die('Unknown route.');
                    break;
            }
        }
    }
    add_action('parse_request', 'cashway_parse_request');

    /**
    * Returns the variables for the queries on cashway routes
    *
    * @param Array vars the variables
    *
    * @return Array vars the variables + the added ones
    */
    function cashway_query_vars($vars)
    {
        $vars[] = 'cashway';

        return $vars;
    }
    add_filter('query_vars', 'cashway_query_vars');
}
