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
                if ($this->is_available()) {
                    // Hook in
                  add_filter('woocommerce_checkout_fields', array($this, 'custom_override_checkout_fields'));
                }

                add_action('woocommerce_cart_calculate_fees', 'cashway_surcharge');
                function cashway_surcharge()
                {
                    // Ajout des frais CashWay
                    global $woocommerce;

                    if (is_admin() && !defined('DOING_AJAX')) {
                        return;
                    }
                    $current_gateway = WC()->session->chosen_payment_method;
                    if ($current_gateway != 'woocashway') {
                        return;
                    }

                    $total_amount = $woocommerce->cart->cart_contents_total + $woocommerce->cart->shipping_total;
                    $fee = 0;
                    if ($total_amount == 0) {
                        return 0;
                    } elseif ($total_amount <= 50.00) {
                        $fee = 1.00;
                    } elseif ($total_amount <= 150.00) {
                        $fee = 2.00;
                    } elseif ($total_amount <= 250.00) {
                        $fee = 3.00;
                    } else {
                        $fee = 4.00;
                    }
                    $woocommerce->cart->add_fee('Frais CashWay', $fee, true, '');
                }
            }

            // Our hooked in function - $fields is passed via the filter!
          public function custom_override_checkout_fields($fields)
          {
              $fields['order']['order_comments']['placeholder'] = 'My new placeholder';

              return $fields;
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
                /*if ( get_post_meta( $order->id, '_payment_method', true ) != 'woocashway' ) {
                  return;
                }
                */
              /*  echo $order->payment_method_title;
                echo "<br>";
                echo get_post_meta( $order->id, '_payment_method', true );
                echo "<br>";
                //echo "----SUITE----";
                echo $order->billing_address_1;
                echo $order->billing_email;
                echo $order->billing_phone;
                print_r( $order );
                global $woocommerce;
                // echo "<p><b>Juste avant les messages</b></p>";
                wc_add_notice(__('message 1', 'woocommerce'), 'success');
                wc_add_notice(__('message 2', 'woocommerce'), 'error');
                //die('erreur, il manque un truc');

                /*
                customer id =


                */
                $order = wc_get_order($order_id);

                $customer_id = $order->user_id;
                $customer_name = $order->billing_first_name.' '.$order->billing_last_name;
                $customer_email = $order->billing_email;
                $customer_phone = $order->billing_phone;
                $customer_country = $order->billing_country;
                $customer_city = $order->billing_city;
                $customer_postcode = $order->billing_postcode;
                $customer_address = $order->billing_address_1;

                $order_id = $order_id;
                $order_at = date('Y-m-d#G:i:s#');
                //$order_currency = date('Y-m-d#G:i:s#');
                //$order_totla = date('Y-m-d#G:i:s#');

                $customer = new WC_Customer();
                global $woocommerce;
                $customer = $woocommerce->customer;

                // print_r( $customer );
                // echo "<br><br><br>";
                // print_r( $order );
                // echo "<br><br><br>";
                // $total_price = $order->get_total();
                // print_r( $total_price );
                // echo "<br><br><br>";
                // echo '$user_id = ' . $customer_id;
                // echo "<br><br><br>";
                // $order_currency = $order->get_order_currency();
                // print_r( $order_currency );
                // echo "<br><br><br>";
                // die();

                $total_price = $order->get_total();
                $order_currency = $order->get_order_currency();

                $headers = array(
                   'Authorization' => 'Basic '.base64_encode($this->cashway_login.':'.$this->cashway_password),
                   'content-type' => 'application/json',
                );
                $body = array(
                    'customer' => array(
                        'id' => $customer_id,
                        'name' => $customer_name,
                        'email' => $customer_email,
                        'phone' => $customer_phone,
                        'country' => $customer_country,
                        'city' => $customer_city,
                        'zipcode' => $customer_postcode,
                        'address' => $customer_address,
                    ),
                    'order' => array(
                        'id' => $order_id,
                        'at' => date('Y-m-dTH:i:sZ'),
                        'currency' => $order_currency,
                        'total' => $total_price,
                    ),
                );
                // Setup variable for wp_remote_post
                $args = array(
                    'method' => 'POST',
                    'headers' => $headers,
                    'body' => json_encode($body),
                );
                $response = wp_remote_post('https://api-staging.cashway.fr/1/transactions', $args);

                // print_r($response);

                // echo 'XXX<br>';
                /*
                print_r($response_body);
                echo 'XXX<br>';
                echo gettype($response_body);
                echo 'barcode = ';
                echo $response_body->barcode;*/
                //die();

                $code = $response['response']['code'];
                if ($code == 201) {
                    $response_body = json_decode($response['body']);
                    $barcode = $response_body->barcode;
                    update_post_meta($order_id, 'cashway_barcode', sanitize_text_field($barcode));
                    $order = wc_get_order($order_id);
                    // Mark as on-hold (we're awaiting the cheque)
                    $order->update_status('on-hold', __('Awaiting cheque payment', 'woocommerce'));
                    // Reduce stock levels
                    $order->reduce_order_stock();
                    // Remove cart
                    WC()->cart->empty_cart();
                    // Return thankyou redirect
                    return array(
                        'result' => 'success',
                        'redirect' => $this->get_return_url($order),
                    );
                } else {
                    die('Une erreur est survenue durant la commande via CashWay.');
                }
            }

            /**
             * Output for the order received page.
             */
            public function receipt_page($order)
            {
                echo $this->generate_cashway_form($order);
            }

            /**
             * Output for the order received page.
             */
            public function thankyou_page($order_id)
            {
                // confirmation de la commande
                $headers = array(
                   'Authorization' => 'Basic '.base64_encode($this->cashway_login.':'.$this->cashway_password),
                   'content-type' => 'application/json',
                );
                $body = array(
                    'order_id' => $order_id,
                );
                $args = array(
                    'method' => 'POST',
                    'headers' => $headers,
                    'body' => json_encode($body),
                );
                $cashway_barcode = get_post_meta($order_id, 'cashway_barcode', true);
                $response = wp_remote_post('https://api-staging.cashway.fr/1/transactions/'.$cashway_barcode.'/confirmation', $args);

                //print_r($response);

                $code = $response['response']['code'];
                if ($code == 201) {
                    $response_body = json_decode($response['body']);
                    $frais_client_cashway = $response_body->customer_fee;
                    $cout_total_client_cashway = $response_body->customer_payment;
                    $barcode_cashway_url = $response_body->barcode_png_url;
                    echo 'Coût supplémentaire CashWay = '.$frais_client_cashway.' €';
                    echo "<img src='$barcode_cashway_url' style='height:60px'/>";
                } else {
                    die('Une erreur est survenue durant la confirmation de la commande via CashWay.');
                }
                die();
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
