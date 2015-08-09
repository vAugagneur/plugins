<?php
/**
 * CashWay API wrapper library.
 *
 * @author    hupstream <mailbox@hupstream.com>
 * @copyright 2015 Epayment Solution - CashWay (http://www.cashway.fr/)
 * @license   Apache License 2.0
 * @link      https://github.com/cshw/api-helpers
 *
 * PHP version 5.3
*/

namespace CashWay;

const VERSION = '0.4.4';

const API_URL = 'https://api.cashway.fr';

const API_URL_STAGING = 'https://api-staging.cashway.fr';

const ENV = 'production';

const PHP_MIN_VERSION = '5.4';

/**
 * Is your system PHP supported (that is, has not been EOL'd yet)?
 * See http://php.net/releases/
 *
 * @return boolean
*/
function isPHPVersionSupported()
{
    return (version_compare(phpversion(), PHP_MIN_VERSION) >= 0);
}

/**
*/
class Log
{
    public static function echolog($s)
    {
        echo date('[c]'), ' ', $s, "\n";
    }

    public static function info($s)
    {
        self::echolog('INFO: ' . $s);
    }

    public static function warn($s)
    {
        self::echolog('WARNING: ' . $s);
    }

    public static function error($s)
    {
        self::echolog('ERROR: ' . $s);
    }
}

/**
 *
*/
class Fee
{
    /**
     * @param float $total_amount  full taxes included total amount for order
     * @param float $customer_part [0..1] how much of this fee the customer pays
     *
     * @return float customer fee in EUR.
    */
    public static function getCartFee($total_amount = 0.0, $customer_part = 1.0)
    {
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

        return round($fee * $customer_part, 2);
    }
}

/**
 * Helpers to integrate and use api.cashway.fr with online shop platforms.
*/
class API
{
    /**
     * Is $data received really signed with our $secret?
     * See https://help.cashway.fr/shops/#recevoir-des-notifications
     * Typical usage:
     *
     * <code>
     * $headers = array_change_key_case(getallheaders(), CASE_LOWER);
     * $data = file_get_contents('php://input');
     * if (CashWay::API::isDataValid($data, $shared_secret, $headers['x-cashway-signature'])) {
     *     // $data is correct
     * }
     * </code>
     *
     * @param string $data received $data we are verifying
     * @param string $secret shared secret between parties, used to sign $data
     * @param string $signature received signature of $data, in the form "algo=value"
     *
     * @return boolean
    */
    public static function isDataValid($data, $secret, $signature)
    {
        $signature = explode('=', $signature);

        return hash_hmac($signature[0], $data, $secret, false) === $signature[1];
    }

    /**
     * @api
    */
    public function __construct($conf)
    {
        /**
         * Configuration.
         *
         * array(
         *   'API_KEY'  => '',
         *   'API_SECRET' => '',
         *   'USER_AGENT' => ''
         * );
        */
        $this->conf = $conf;

        //
        $this->user_agent   = $this->getUserAgent();
        $this->api_base_url = $this->getApiBaseUrl();

        $this->order    = array();
        $this->customer = array();
    }

    private function getUserAgent()
    {
        $ua = array('CashWay/' . \CashWay\VERSION);

        if (array_key_exists('USER_AGENT', $this->conf)) {
            $ua[] = $this->conf['USER_AGENT'];
        }

        $ua[] = 'PHP/' . PHP_VERSION;
        $ua[] = PHP_OS;

        return implode(' ', $ua);
    }

    /**
     * Build API base URL to use:
     * scheme, host, port, base path, version),
     * depending on context.
     *
     * Precedence is: conf['API_URL'] > conf['API_URL_STAGING'] > self::API_URL.
     *
     * @return String
    */
    private function getApiBaseUrl()
    {
        $version = '1';
        $host    = API_URL;

        if (isset($this->conf['USE_STAGING']) && $this->conf['USE_STAGING']) {
            $host = API_URL_STAGING;
        }

        if (isset($this->conf['API_URL'])) {
            $host = $this->conf['API_URL'];
        }

        return sprintf('%s/%s', $host, $version);
    }

    /**
     * Generic wrapper to set orders.
     * Call setOrder('platform', ...);
     *
     * @api
    */
    public function setOrder()
    {
        $args = func_get_args();

        $platform = array_shift($args);
        $known_platforms = array(
            'prestashop',
            //'magento'
        );

        if (!in_array($platform, $known_platforms)) {
            throw new \DomainException('This platform is not handled yet.');
        }

        $callback = sprintf('setOrder_%s', $platform);
        if (!method_exists($this, $callback)) {
            throw new \DomainException('Unknown method.' . $callback);
        }

        return call_user_func_array(array($this, $callback), $args);
    }

    /**
     * Notify API about transaction, get diagnostics data:
     * about the shop, the order, the transaction.
    */
    public function evaluateTransaction()
    {
        $payload = json_encode(
            array(
            'agent'    => $this->user_agent,
            'order'    => $this->order,
            'customer' => $this->customer
            )
        );

        return $this->httpPost('/transactions/hint', $payload);
    }

    /**
     * Open a confirmed CashWay transaction for the set order.
     *
     * @api
     *
     * @return array
    */
    public function openTransaction($force_confirm = false)
    {
        $payload = array(
            'agent'    => $this->user_agent,
            'order'    => $this->order,
            'customer' => $this->customer
        );

        if ($force_confirm) {
            $payload['confirm'] = true;
        }

        return $this->httpPost('/transactions/', json_encode($payload));
    }

    /**
     * @api
     *
     * @return array
    */
    public function confirmTransaction($transaction_id, $order_id = null, $email = null, $phone = null)
    {
        $payload = json_encode(
            array(
            'agent'      => $this->user_agent,
            'order_id'   => $order_id,
            'email'      => $email,
            'phone'      => $phone
            )
        );

        return $this->httpPost(sprintf('/transactions/%s/confirmation', $transaction_id), $payload);
    }

    /**
     * Report a failed payment to CashWay, in order to be notified
     * x minutes later if no subsequent order has been made.
     *
     * @api
     *
     * @param string   $order_id         order or cart id
     * @param float    $order_amount
     * @param string   $customer_id
     * @param string   $customer_email
     * @param string   $provider       that just failed
     * @param string   $reason         of the failure
     *
     * @return Array
    */
    public function reportFailedPayment(
        $order_id,
        $order_amount,
        $customer_id,
        $customer_email,
        $provider,
        $reason
    ) {
        $payload = json_encode(
            array(
                'event' => 'payment_failed',
                'created_at' => date('c'),
                'provider' => $provider,
                'reason' => $reason,
                'order' => array(
                    'id' => $order_id,
                    'total' => $order_amount
                ),
                'customer' => array(
                    'id' => $customer_id,
                    'email' => $customer_email
                )
            )
        );

        return $this->httpPost('/shops/me/events', $payload);
    }

    public function registerAccount($params)
    {
        return $this->httpPost('/shops', json_encode($params));
    }

    /**
     * Update account.
     * See https://help.cashway.fr/shops/#notimpl-mettre--jour-le-compte
     *
     * <code>
     * $api->updateAccount(array(
     *     'notification_url' => 'http://...',
     *     'shared_secret' => 'ABCD'
     * ));
     * </code>
     *
     * @api
     *
     * @param Array $params
     *
     * @return Array
    */
    public function updateAccount($params)
    {
        return $this->httpPost('/shops/me', json_encode($params));
    }

    public function checkTransactionsForOrders($order_ids)
    {
        return $this->httpGet(sprintf('/shops/me/transactions'));
    }

    public function httpPost($path, $payload)
    {
        return $this->httpDo('POST', $path, $payload);
    }

    public function httpGet($path, $query = array())
    {
        return $this->httpDo('GET', $path, $query);
    }

    public function httpDo($verb, $path, $query)
    {
        if (!in_array($verb, array('GET', 'POST'))) {
            return array('errors' => array(array(
                'code' => 'method_not_supported',
                'status' => 0
            )));
        }

        $ret  = null;
        $auth = null;
        $url  = $this->api_base_url . $path;

        if (isset($this->conf['API_KEY']) && $this->conf['API_KEY'] != '') {
            $auth = implode(
                ':',
                array($this->conf['API_KEY'],
                      $this->conf['API_SECRET'])
            );
        }

        switch($verb) {
            case 'GET':
                $headers  = array('Accept: application/json');
                $query    = http_build_query($query);
                $transfer = cURL::GET(
                    $url . '?' . $query,
                    $auth,
                    $headers,
                    $this->user_agent
                );
                break;
            case 'POST':
                if (!is_string($query)) {
                    $query = json_encode($query);
                }
                $headers = array(
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'Content-Length: ' . strlen($query)
                );
                $transfer = cURL::POST(
                    $url,
                    $query,
                    $auth,
                    $headers,
                    $this->user_agent
                );
                break;
        }

        if ($transfer['body'] === false) {
            $ret = array('errors' => array(array(
                'code'   => 'curl_error',
                'status' => $transfer['error']
            )));
        } else {
            $ret = json_decode($transfer['body'], true);
        }

        return $ret;
    }

    /**
     * Prestashop-specific setup.
     *
     * @uses \Customer, \AddressCore, \Cart, \Shop to retrieve details.
     *
     * @param string   $id       order or cart id
     * @param Cart     $cart     as returned by $this->context->cart
     * @param Customer $customer as returned by $this->context->customer
     * @param string   $language ISO code (FR)
     * @param string   $currency ISO code (EUR)
     *
     * @return void
    */
    // @codingStandardsIgnoreLine
    private function setOrder_prestashop($id, $cart, $customer, $language, $currency)
    {
        $address = new \AddressCore($cart->id_address_delivery);

        $products = $cart->getProducts();
        $details  = array();
        foreach ($products as $prod) {
            $details[] = array(
                'name'     => $prod['name'],
                'quantity' => $prod['cart_quantity'],
                'price'    => $prod['price'],
                'total'    => $prod['total'],
                'rate'     => $prod['rate']
            );
        }

        $this->order =  array(
            // required
            // FIXME. This is the cart id, not the order id.
            'id'          => $id,
            'at'          => $cart->date_add,
            'currency'    => $currency,
            'total'       => $cart->getOrderTotal(true, \Cart::BOTH),
            'language'    => $language,
            'items_count' => $cart->nbProducts(),
            // optional
            'details'     => $details
        );

        $this->customer = array(
            // required
            'id'         => $customer->id,
            'name'       => $customer->firstname . ' ' . $customer->lastname,
            'email'      => $customer->email,
            'phone'      => array($address->phone, $address->phone_mobile),
            // optional
            'company'    => $customer->company,
            'siret'      => $customer->siret,
            'ape'        => $customer->ape,
            'risk'       => $customer->id_risk,
            'created_at' => $customer->date_add,
            'geoloc'     => array(
                'country'  => $customer->geoloc_id_country,
                'state'    => $customer->geoloc_id_state,
                'postcode' => $customer->geoloc_postcode
            )
        );
    }
}

/**
 * Simple cURL wrapper.
*/
// @codingStandardsIgnoreLine
class cURL
{
    /**
     * Curl-based HTTP GET action.
     *
     * @param string $url
     * @param string $auth
     * @param array  $headers
     * @param string $user_agent
     *
     * @return array('body' => string, 'error' => string)
    */
    public static function GET($url, $auth, $headers, $user_agent)
    {
        $opts = array(
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_USERAGENT  => $user_agent
        );

        if (null !== $auth) {
            $opts[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
            $opts[CURLOPT_USERPWD]  = $auth;
        }

        return self::curlDo($url, $opts);
    }

    /**
     * Curl-based HTTP POST action.
     *
     * @param string $url
     * @param string $payload
     * @param string $auth
     * @param array  $headers
     * @param string $user_agent
     *
     * @return array('body' => string, 'error' => string)
    */
    public static function POST($url, $payload, $auth, $headers, $user_agent)
    {
        $opts = array(
            CURLOPT_POST       => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_USERAGENT  => $user_agent,
        );

        if (null !== $auth) {
            $opts[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
            $opts[CURLOPT_USERPWD]  = $auth;
        }

        return self::curlDo($url, $opts);
    }

    public static function curlDo($url, $options)
    {
        $error = false;
        $body  = false;

        $base_options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FORBID_REUSE   => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        );

        $ch = curl_init($url);

        if (!(curl_setopt_array($ch, $base_options)
            && curl_setopt_array($ch, $options))
        ) {
            $error = 'curl (x): failed to set options.';
        } else {
            $body = curl_exec($ch);

            if (false === $body) {
                $error = sprintf(
                    'curl (%d): %s',
                    curl_errno($ch),
                    curl_error($ch)
                );
            }
        }
        curl_close($ch);

        return array(
            'body'  => $body,
            'error' => $error
        );
    }
}
