<?php
/**
 * 2015 CashWay
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
 *  @author    CashWay <contact@cashway.fr>
 *  @copyright 2015 CashWay
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class CashwayNotificationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $res = \CashWay\API::receiveNotification(
            Tools::file_get_contents('php://input'),
            getallheaders(),
            Configuration::get('CASHWAY_SHARED_SECRET')
        );

        if ($res[0] === false) {
            $this->terminateReply(400, $res[1]);
        }

        $event = $res[1];
        $this->data = $res[2];

        $handler = $this->snakeToCamel('on_'.$event);

        method_exists($this, $handler) ?
            $this->$handler() :
            $this->terminateReply(400, 'Do not know how to handle this event.');
    }

    /**
     * From this_snake_case to thisSnakeCase
    */
    private function snakeToCamel($val)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $val))));
    }

    private function onConversionExpired()
    {
        if (!Configuration::get('CASHWAY_SEND_EMAIL')) {
            $this->terminateReply(202, 'Ok, but not sending email per shop config.');
        }

        $order = new Order((int)$this->data->order_id);
        if (!Validate::isLoadedObject($order)) {
            $this->terminateReply(404, 'Could not find such an order.');
        }

        $customer = new Customer($order->id_customer);
        $reorder_url = $this->context->link->getPageLink(
            'order',
            true,
            $this->context->language->id,
            array('submitReorder' => '1',
                'id_order' => (int)$order->id)
        );

        Mail::Send(
            $this->context->language->id,
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
            $this->context->shop->id
        );

        $this->terminateReply(201, 'Call back email sent.');
    }

    private function onTransactionPaid()
    {
        // FIXME, port code from this function directly here.
        $this->onGenericCheck();
    }

    private function onTransactionExpired()
    {
        $this->onGenericCheck();
    }

    private function onTransactionConfirmed()
    {
        $this->onGenericCheck();
    }

    private function onStatusCheck()
    {
        $this->onGenericCheck();
    }

    private function onGenericCheck()
    {
        ob_start();
        CashWay::checkForPayments();
        $this->terminateReply(200, array(
            'fn' => 'checkForPayments',
            'log' => explode("\n", ob_get_clean()),
            'agent' => 'CashWayModule/'.Cashway::VERSION.' PrestaShop/'._PS_VERSION_.' PHP/'.PHP_VERSION.' '.PHP_OS
        ));
    }

    private function terminateReply($code, $message)
    {
        $codes = array(
            200 => array('200 OK',          true),
            201 => array('201 Created',     true),
            202 => array('202 Accepted',    true),
            400 => array('400 Bad Request', false)
        );

        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(array(
            'status'  => $codes[$code][1] ? 'ok' : 'error',
            'message' => $message
        ));
        exit;
    }
}
