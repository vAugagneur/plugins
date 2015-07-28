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
            <a class="cashway" style="padding: 15px 40px 17px 99px; background: url(https://www.cashway.fr/wp-content/uploads/2015/07/cadenas_couleur-e1436360740191.png) 30px 19px no-repeat #fbfbfb;" href="{$link->getModuleLink('cashway', 'payment', [], true)|escape:'html'}" title="{l s='Paiement en espèces sur internet' mod='cashway'}">
            <img width="700px" src="https://www.cashway.fr/wp-content/uploads/2015/07/logo_couleur.png">
            <span style="float:right;"><img src="https://www.cashway.fr/wp-content/uploads/2015/07/1436374949_coins.png" width="70px"></span>
            <br>
                <span>
                {l s='Vous payez en espèces chez un buraliste près de chez vous, la commande sera automatiquement validée. (Frais de traitement : ' mod='cashway'}{$cart_fee|escape}&nbsp; )
                </span>
            </a>
        </p>
    </div>
</div>

{elseif $template_type == 'normal'}

<div class="row">
    <div class="col-xs-12 col-md-12">
        <p class="payment_module">
            <a class="cashway" style="padding: 15px 40px 17px 99px; color:#000; background: url(https://www.cashway.fr/wp-content/uploads/2015/07/cadenas-e1436359348647.png) 30px 19px no-repeat #ff8f02; " href="{$link->getModuleLink('cashway', 'payment', [], true)|escape:'html'}" title="{l s='Paiement en espèces sur internet' mod='cashway'}">
            <img width="700px" src="https://www.cashway.fr/wp-content/uploads/2015/07/vignetteCWblanc_petitcadenas_détouré-copy1.png">
            <span style="float:right;"><img src="https://www.cashway.fr/wp-content/uploads/2015/07/1436374949_coins.png" width="70px"></span>
                <br>
                <span style="color:#FFF;">
                {l s='Vous payez en espèces chez un buraliste près de chez vous, la commande sera automatiquement validée. (Frais de traitement : ' mod='cashway'}{$cart_fee|escape}&nbsp; )
                </span>
            </a>
        </p>
    </div>
</div>

{/if}
