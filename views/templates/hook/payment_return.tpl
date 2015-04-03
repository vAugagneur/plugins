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

{if $status == 'ok'}

	<p>Pour régler les <span class="price"><strong>{$total_to_pay|escape}</strong></span> de votre commande,
		vous pouvez maintenant vous rendre dans un des points de paiement
		indiqués sur la carte ci-dessous, muni du code barre suivant, dans les 3 jours
		(le code barre n'est valide que jusqu'au {$expires|escape})&nbsp;:
	</p>
	<p>
		<img src="{$cashway_api_url|escape}/1/barcode/{$barcode|escape:'url'}" alt="{$barcode|escape:'html'}" title="{$barcode|escape:'htmlall'}" />
		<span style="display: block;
					letter-spacing: 0.4rem;
					color: black;
					font-family: courier, mono;
					font-size: 1.6rem;
					align: center;">
			{$barcode|escape:'htmlall'}</span>
	</p>

	<p>
	{if !isset($reference)}
		{l s='Please note and keep your order number #%d.' sprintf=$id_order mod='cashway'}
	{else}
		{l s='Please note and keep your order reference %s.' sprintf=$reference mod='cashway'}
	{/if}
	</p>

	<p>
		{l s='An email and a text message have been sent to you with all this information.' mod='cashway'}
		<br /><br /><strong>{l s='Your order will be sent as soon as we receive your payment.' mod='cashway'}</strong>
	</p>

		<div id="map-canvas" style="width: 100%; height: 400px;"></div>
		<input id="address" type="textbox" value="{$location.search|escape:'htmlall'}" onsubmit="codeLocation();" />
		<input type="button" value="Trouver les points de paiement CashWay autour de cette adresse" onclick="codeLocation();" />

		<br /><br />{l s='For any questions or for further information, please contact our' mod='cashway'} <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='customer service department.' mod='cashway'}</a>.

	</p>
{else}
	<p class="warning">
	{if $barcode == '-failed-'}
		{l s='The CashWay service is currently not available to register this order.' mod='cashway'}
		{l s='Your order is intact. If you will, you may use an other payment method.' mod='cashway'}
		<a href="/index.php?controller=order&step=3" class="exclusive_large">{l s='Choose an other payment method' mod='cashway'}</a>
	{else}
		{l s='We have noticed that there is a problem with your order. If you think this is an error, you can contact our' mod='cashway'}
		<a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='customer service department.' mod='cashway'}</a>.
	{/if}
	</p>
{/if}
