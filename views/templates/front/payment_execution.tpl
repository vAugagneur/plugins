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
	<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}"
		title="{l s='Go back to the Checkout' mod='cashway'}">{l s='Checkout' mod='cashway'}</a>
	<span class="navigation-pipe">{$navigationPipe|escape:'html'}</span>
	{l s='Check payment' mod='cashway'}
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

	{if $available.0}

	<form action="{$link->getModuleLink('cashway', 'validation', [], true)|escape:'html'}"
		method="post">

		<ul>
			<li><p>Votre commande s&rsquo;élève à <span id="amount" class="price">{displayPrice price=$total}</span>
				{if $use_taxes == 1}{l s='(taxes incluses)' mod='cashway'}{/if}.</p></li>
			{if $cart_fee > 0}
			<li><p>Frais destinés à votre buraliste&nbsp;: {displayPrice price=$cart_fee}.</p></li>
			{/if}
			<li><p>Après confirmation de votre commande,
				vous recevrez par email et par SMS un code
				à présenter chez un des buralistes
				présents sur la carte ci-dessous.</p>
				<p>Celui pourra ainsi encaisser et valider votre paiement.</p>
				<p>Votre commande sera alors immédiatement traitée par nos services.</p></li>
			<li><p><strong>Total à payer au buraliste&nbsp;:
				<span id="amount" class="price">{displayPrice price=$total + $cart_fee}</span>.</strong></p></li>
		</ul>

		<p><b>{l s='Please confirm your order by clicking \'I confirm my order\'.' mod='cashway'}</b></p>
		<p class="cart_navigation" id="cart_navigation">
			<input type="image" src="{$this_path_cashway|escape:'urlpathinfo'}/views/img/cashway-confirm.png" alt="{l s='I confirm my order' mod='cashway'}"/>
			<a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html'}">{l s='Other payment methods' mod='cashway'}</a>
		</p>
	</form>
	{else}
		<p><strong>Hélas&nbsp;: cette méthode de paiement est temporairement indisponible</strong>.
			Nous mettons tout en œuvre pour la rétablir le plus tôt possible.
			<span>{$available.1|escape}</span></p>
		<p><a class="exclusive_large" href="/index.php?controller=order&step=3">Vous pouvez choisir une autre méthode</a></p>
		<br>
	{/if}
	<h4>Les points de paiement présents autour de votre adresse&nbsp;:</h4>
	<input id="cashway-map-search" type="textbox" value="{$location.search|escape:'html'}" />
	<input id="cashway-map-search-btn" type="button" value="Trouver les points de paiement CashWay autour de cette adresse" />
	<div id="cashway-map-canvas" style="width: 100%; height: 400px;"></div>
	<script src="https://maps.cashway.fr/js/cwm.min.js" defer async></script>
{/if}
