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
    <div class="col-xs-12 col-md-12">
        <p class="payment_module">
            <a class="cashway" style="padding: 15px 40px 17px 99px; background: url({$this_path_cashway|escape:'urlpathinfo':'UTF-8'}views/img/btns/cadenas-couleurs.png) 30px 19px no-repeat #fbfbfb;" href="{$link->getModuleLink('cashway', 'payment', [], true)|escape:'htmlall':'UTF-8'}" title="{l s='Paiement en espèces sur internet' mod='cashway'}">
            <img width="700px" src="{$this_path_cashway|escape:'urlpathinfo':'UTF-8'}views/img/btns/cashway-peesi-couleurs.png">
            <span style="float:right;"><img src="{$this_path_cashway|escape:'urlpathinfo':'UTF-8'}views/img/btns/coins.png" width="70px"></span>
            <br>
                <span>
                {l s='Vous payez en espèces chez un buraliste près de chez vous, la commande sera automatiquement validée. (Frais de traitement : ' mod='cashway'}{$cart_fee|escape:'htmlall':'UTF-8'}&nbsp; )
                </span>
            </a>
        </p>
    </div>
</div>
<img src="{$cashway_api_base_url|escape:'htmlall':'UTF-8'}/n/pu/offered?v=col" alt="" />
{elseif $template_type == 'normal'}

<div class="row">
    <div class="col-xs-12 col-md-12">
        <p class="payment_module">
            <a class="cashway" style="padding: 15px 40px 17px 99px; color:#000; background: url({$this_path_cashway|escape:'urlpathinfo':'UTF-8'}views/img/btns/cadenas-blanc.png) 30px 19px no-repeat #ff8f02; " href="{$link->getModuleLink('cashway', 'payment', [], true)|escape:'htmlall':'UTF-8'}" title="{l s='Paiement en espèces sur internet' mod='cashway'}">
            <img width="700px" src="{$this_path_cashway|escape:'urlpathinfo':'UTF-8'}views/img/btns/cashway-peesi-blanc.png">
            <span style="float:right;"><img src="{$this_path_cashway|escape:'urlpathinfo':'UTF-8'}views/img/btns/coins.png" width="70px"></span>
                <br>
                <span style="color:#FFF;">
                {l s='Vous payez en espèces chez un buraliste près de chez vous, la commande sera automatiquement validée. (Frais de traitement : ' mod='cashway'}{$cart_fee|escape:'htmlall':'UTF-8'}&nbsp; )
                </span>
            </a>
        </p>
    </div>
</div>
<img src="{$cashway_api_base_url|escape:'htmlall':'UTF-8'}/n/pu/offered?v=sim" alt="" />
{/if}
