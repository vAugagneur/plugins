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

{capture name=path}
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='cashway'}">{l s='Checkout' mod='cashway'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Check payment' mod='cashway'}
{/capture}

{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Order summary' mod='cashway'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if isset($nbProducts) && $nbProducts <= 0}
	<p class="warning">{l s='Your shopping cart is empty.' mod='cashway'}</p>
{else}

<h3>{l s='Paiement avec ' mod='cashway'}
	<img src="{$this_path_cashway|escape:'urlpathinfo'}/views/img/cashway-180x40.png"
		alt="{l s='CashWay' mod='cashway'}"
		width="180"
		height="40"
		style="margin: 0px 10px 5px 0px;" /></h3>

<form action="{$link->getModuleLink('cashway', 'validation', [], true)|escape:'html'}"
	method="post">

	<ul>
		<li><p>Votre commande s&rsquo;élève à <span id="amount" class="price">{displayPrice price=$total}</span>
			{if $use_taxes == 1}{l s='(taxes incluses)' mod='cashway'}{/if}.</p></li>
		{if $cart_fee > 0}
		<li><p>En y ajoutant {displayPrice price=$cart_fee},
			avec CashWay, vous pouvez régler en espèces vos achats.</p></li>
		{/if}
		<li><p>Après confirmation de votre commande,
			vous recevrez par email et par SMS un code-barre
			à présenter chez un des buralistes
			présents sur la carte ci-dessous.
			Celui pourra ainsi encaisser et valider votre paiement.
			Votre commande sera alors immédiatement livrée.</p></li>
		<li><p><strong>Total à payer au buraliste&nbsp;:
			<span id="amount" class="price">{displayPrice price=$total + $cart_fee}</span>.</strong></p></li>
	</ul>

	<p><b>{l s='Please confirm your order by clicking \'I confirm my order\'.' mod='cashway'}</b></p>
	<p class="cart_navigation" id="cart_navigation">
		<input type="submit" value="{l s='I confirm my order' mod='cashway'}" class="exclusive_large"/>
		<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}" class="button_large">{l s='Other payment methods' mod='cashway'}</a>
	</p>
</form>

<div id="map-canvas" style="width: 100%; height: 400px;"></div>
<input id="address" type="textbox" value="{$location.search|escape:'html'}" onsubmit="codeLocation();" />
<input type="button" value="Trouver les points de paiement CashWay autour de cette adresse" onclick="codeLocation();" />
<script>window.ENV = '{$env|escape}';</script>
<script src="{$this_path_cashway|escape:'urlpathinfo'}/views/js/cashway_map.js"></script>
{/if}
