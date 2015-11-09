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

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_0_11_2($module)
{
    // Fix misattribution of order status config value:
    // - if it stored the string label, we make it store the actual value associated with the label.
    // - if it is empty, we reset it.
    //
    $current_cop = Configuration::get('CASHWAY_OS_PAYMENT');
    if (in_array($current_cop, array('PS_OS_PAYMENT', 'PS_OS_WS_PAYMENT'))) {
        Configuration::updateValue('CASHWAY_OS_PAYMENT', (int)Configuration::get($current_cop));

    } elseif ($current_cop == '') {
        Configuration::updateValue('CASHWAY_OS_PAYMENT', (int)Configuration::get('PS_OS_WS_PAYMENT'));
    }

    $module->updateNotificationParameters();

    return true;
}