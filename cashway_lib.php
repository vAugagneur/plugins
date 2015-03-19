<?php
/**
 * CashWay API wrapper library.
 *
 * @link https://github.com/cshw/api-helpers
 *
 * @copyright 2015 Epayment Solution - CashWay (http://www.cashway.fr/)
 * @license   Apache License 2.0
 * @author    hupstream <mailbox@hupstream.com>
*/

namespace CashWay;

const VERSION = '0.1.1';

const API_URL = 'https://api.cashway.fr';

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
    public static function echolog($s) { echo date('[c]'), ' ', $s, "\n"; }
    public static function info($s)  { self::echolog('INFO: ' . $s); }
    public static function warn($s)  { self::echolog('WARNING: ' . $s); }
    public static function error($s) { self::echolog('ERROR: ' . $s); }
}

/**
 *
*/
class Fee
{
    /**
     * @param float $total_amount full taxes included total amount for order
     * @param float $customer_fee_part [0..1] how much of this fee is paid by the customer
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

		if (array_key_exists('USER_AGENT', $this->conf))
			$ua[] = $this->conf['USER_AGENT'];

		$ua[] = 'PHP/' . PHP_VERSION;
		$ua[] = PHP_OS;

		return implode(' ', $ua);
	}

    /**
     * Build API base URL to use:
     * scheme, host, port, base path, version),
     * depending on context.
     *
     * @return String
    */
    private function getApiBaseUrl()
    {
        $host    = isset($this->conf['API_URL']) ? $this->conf['API_URL'] : API_URL;
        $version = '1';

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
	 * TODO: notify API about transaction, get diagnostics data.
	*/
	public function evaluateTransaction()
	{
		return null;
	}

    /**
     * Open a confirmed CashWay transaction for the set order.
	 *
     * @api
     *
     * @return array
    */
    public function openTransaction()
    {
        $payload = json_encode(array(
            'agent'    => $this->user_agent,
            'order'    => $this->order,
            'customer' => $this->customer
        ));

        return $this->httpPost('/transactions/', $payload);
    }

    /**
     * @api
     *
     * @return array
    */
    public function confirmTransaction($transaction_id, $order_id = null, $email = null, $phone = null)
    {
        $payload = json_encode(array(
            'agent'      => $this->user_agent,
			'order_id'   => $order_id,
            'email'      => $email,
            'phone'      => $phone
        ));

        return $this->httpPost(sprintf('/transactions/%s/confirm', $transaction_id), $payload);
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
            return;
        }

        $ret  = null;
        $url  = $this->api_base_url . $path;
        $auth = implode(':', array($this->conf['API_KEY'],
                                   $this->conf['API_SECRET']));

        switch($verb) {
            case 'GET':
				$headers = array(
					'Accept: application/json'
				);
                $query    = http_build_query($query);
                $transfer = cURL::GET($url . '?' . $query, $auth, $headers, $this->user_agent);
                break;
            case 'POST':
                $headers = array(
                    'Content-Type: application/json',
					'Accept: application/json',
                    'Content-Length: ' . strlen($query)
                );
                $transfer = cURL::POST($url, $query, $auth, $headers, $this->user_agent);
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
     * @param Cart as returned by $this->context->cart
     * @param Customer as returned by $this->context->customer
     * @param string $language ISO code (FR)
     * @param string $currency ISO code (EUR)
     *
     * @return void
    */
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
            'id'          => $customer->id,
            'name'        => $customer->firstname . ' ' . $customer->lastname,
            'email'       => $customer->email,
            'phone'       => array($address->phone, $address->phone_mobile),
            // optional
            'company'     => $customer->company,
            'siret'       => $customer->siret,
            'ape'         => $customer->ape,
            'risk'        => $customer->id_risk,
            'created_at'  => $customer->date_add,
            'geoloc'      => array(
                'country'   => $customer->geoloc_id_country,
                'state'     => $customer->geoloc_id_state,
                'postcode'  => $customer->geoloc_postcode
            )
        );
    }
}

/**
 * Simple cURL wrapper.
*/
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
        return self::curlDo(
            $url,
            array(
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_USERAGENT      => $user_agent,
                CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
                CURLOPT_USERPWD        => $auth
            )
        );
    }

    /**
     * Curl-based HTTP POST action.
     *
     * @param string $path
     * @param string $payload
     * @param string $auth
     * @param array  $headers
     * @param string $user_agent
     *
     * @return array('body' => string, 'error' => string)
    */
    public static function POST($url, $payload, $auth, $headers, $user_agent)
    {
        return self::curlDo(
            $url,
            array(
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_USERAGENT      => $user_agent,
                CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
                CURLOPT_USERPWD        => $auth
            )
        );
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

        if (!(curl_setopt_array($ch, $base_options) && curl_setopt_array($ch, $options))) {
            $error = 'curl (x): failed to set options.';
        } else {
            $body = curl_exec($ch);

            if (false === $body) {
                $error = sprintf('curl (%d): %s',
                                 curl_errno($ch),
                                 curl_error($ch));
            }
        }
        curl_close($ch);

        return array(
            'body'  => $body,
            'error' => $error
        );
    }
}
