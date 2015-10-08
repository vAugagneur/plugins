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

class CashwayStatusModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        header('Content-Type: text/plain; charset=utf-8');
        if (!$this->module->active) {
            header('HTTP/1.1 404 Not Found');
        } elseif ($this->checkAccess())
            CashWay::checkForPayments();
        else {
            header('HTTP/1.1 403 Forbidden');
        }

        die;
    }
}
