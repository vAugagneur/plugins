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

{if $template_type == 'light'}
<div class="row">
    <div class="col-xs-12">
        <p class="payment_module">
            <a class="cheque cashway"
                style="background-image: url({$this_path_cashway|escape:'urlpathinfo':'UTF-8'}views/img/cashway-payment.png);"
                href="{$link->getModuleLink('cashway', 'payment', [], true)|escape:'htmlall':'UTF-8'}"
                title="{l s='Paiement en espèces sur internet' mod='cashway'}">
                {l s='Payer en espèces avec CashWay, chez un buraliste près de chez vous'}
                <span>(frais de traitement&nbsp;: {$cart_fee|escape:'htmlall':'UTF-8'})</span>
            </a>
        </p>
    </div>
</div>
<img src="{$cashway_api_base_url|escape:'htmlall':'UTF-8'}/n/pu/offered?p=ps&amp;v=col" alt="" />
{elseif $template_type == 'normal'}
<div class="row">
    <div class="col-xs-12">
        <p class="payment_module">
            <a class="cheque cashway"
                style="background-color: white; background-image: url({$this_path_cashway|escape:'urlpathinfo':'UTF-8'}views/img/cashway-payment-orange.png); border-color: orange;"
                href="{$link->getModuleLink('cashway', 'payment', [], true)|escape:'htmlall':'UTF-8'}"
                title="{l s='Paiement en espèces sur internet' mod='cashway'}">
                {l s='Payer en espèces avec CashWay, chez un buraliste près de chez vous'}
                <span>(frais de traitement&nbsp;: {$cart_fee|escape:'htmlall':'UTF-8'})</span>
            </a>
        </p>
    </div>
</div>
<img src="{$cashway_api_base_url|escape:'htmlall':'UTF-8'}/n/pu/offered?p=ps&amp;v=sim" alt="" />
{/if}
