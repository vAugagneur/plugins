{*
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
*}

<div class="row"><div class="col-xs-12 col-md-6">
    <p class="payment_module" style="border: 1px solid orange;">
    	<a class="cashway" href="{$link->getModuleLink('cashway', 'payment', [], true)|escape:'html'}" title="{l s='Pay with CashWay' mod='cashway'}">
            {$cart_fee|escape}&nbsp;: {l s='Payer avec CashWay' mod='cashway'}
            <br>
            <span>
            {l s='Vous payez en espèces chez un buraliste près de chez vous, la commande sera alors validée.' mod='cashway'}
            </span>
    	</a>
    </p>
</div></div>