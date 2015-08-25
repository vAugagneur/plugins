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
    <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'htmlall':'UTF-8'}"
        title="{l s='Go back to the Checkout' mod='cashway'}">{l s='Checkout' mod='cashway'}</a>
    <span class="navigation-pipe">{$navigationPipe|escape:'htmlall':'UTF-8'}</span>
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
    <img src="{$this_path_cashway|escape:'urlpathinfo':'UTF-8'}/views/img/cashway-180x40.png"
        alt="{l s='CashWay' mod='cashway'}"
        width="180"
        height="40"
        style="margin: 0px 10px 5px 0px;" /></h3>

        {if $available.0}
        <form action="{$link->getModuleLink('cashway', 'validation', [], true)|escape:'htmlall':'UTF-8'}"
            method="post">

            <ul>
                <li><p>
                    Votre commande s&rsquo;élève à <span id="amount" class="price">{displayPrice price=$total}</span>
                    {if $use_taxes == 1}{l s='(taxes incluses)' mod='cashway'}{/if}.
                </p></li>
                {if $cart_fee > 0}
                <li><p>
                    Frais destinés à votre buraliste&nbsp;: {displayPrice price=$cart_fee}.
                </p></li>
                {/if}
                <li><p>
                    Après confirmation de votre commande,
                    vous recevrez par email et par SMS un code
                    à présenter chez un des buralistes
                    présents sur la carte ci-dessous&nbsp;;
                    celui pourra ainsi encaisser et valider votre paiement.
                    Votre commande sera alors immédiatement traitée par nos services.
                </p></li>
			    <li><p><strong>Total à payer au buraliste&nbsp;:
                    <span id="amount" class="price">{displayPrice price=$total + $cart_fee}</span>.</strong></p></li>
            </ul>
            <p><b>{l s='Please confirm your order by clicking \'I confirm my order\'.' mod='cashway'}</b></p>
            {if $info_cgu}
            <p>Merci de valider les conditions d'utilisation pour confirmer votre commande&nbsp;:</p>
            {/if}
            <div class="form-group form-group-sm" {if $info_cgu} style="padding: 0.1rem 0 0.1rem 1rem; background: #fda;" {/if}>
                <div class="checkbox control-label">
                    <input type="checkbox" value="cgu-accept" id="cgu-accept" name="cgu-accept" />
                    <label for="cgu-accept">J'ai lu les conditions d&rsquo;utilisation de CashWay
                        et j&rsquo;y adhère sans réserve</label>
                        (<a href="https://help.cashway.fr/cgu/" target="blank" rel="nofollow" style="color: orange;">lire les CGU CashWay</a>)
                </div>
            </div>
            <p class="cart_navigation" id="cart_navigation">
                {if (rand(1,100) > 50)}
                <input type="hidden" name="btn" value="image"/>
                <input id="cashway-confirm-btn"
                    type="image"
                    src="{$this_path_cashway|escape:'urlpathinfo':'UTF-8'}/views/img/cashway-confirm.png"
                    alt="{l s='I confirm my order' mod='cashway'}"/>
                {else}
                <input type="hidden" name="btn" value="button"/>
                <input id="cashway-confirm-btn"
                    type="submit"
                    class="btn btn-primary button button-primary"
                    value="{l s='I confirm my order' mod='cashway'}"/>
                {/if}
            </p>
            <style>input[disabled] { opacity: 0.5; }</style>
            <script>
            $('#cashway-confirm-btn').prop('disabled', !$('#cgu-accept').is(':checked'));
            $('#cgu-accept').on('click', function (ev) {
                $('#cashway-confirm-btn').prop('disabled', !$(this).is(':checked'));
            });
            </script>
	    </form>
        <img src="{$cashway_api_base_url|escape:'htmlall':'UTF-8'}/n/pu/considered?v=ok" alt="" />
	{else}
        <p><strong>Hélas&nbsp;: cette méthode de paiement est temporairement indisponible</strong>.
            Nous mettons tout en œuvre pour la rétablir le plus tôt possible.
            <span>{$available.1|escape:'htmlall':'UTF-8'}</span></p>
        <p><a class="exclusive_large" href="/index.php?controller=order&step=3">Vous pouvez choisir une autre méthode</a></p>
        <br>
        <img src="{$cashway_api_base_url|escape:'htmlall':'UTF-8'}/n/pu/considered?v=failed&amp;r={$available.2|escape:'htmlall':'UTF-8'}" alt="" />
	{/if}
    <br>
    <h4 id="cashway-map-l">Les distributeurs proches de chez vous&nbsp;:</h4>
    <input id="cashway-map-search"
        type="textbox"
        class="form-control ac_input"
        value="{$location.search|escape:'htmlall':'UTF-8'}" />
    <input id="cashway-map-search-btn"
        type="button"
        class="btn btn-info button button-small"
        value="Trouver les distributeurs CashWay autour de cette adresse" />
    <div id="cashway-map-canvas" style="width: 100%; height: 400px;"></div>
    <script src="https://maps.cashway.fr/js/cwm.min.js" defer async></script>

    <p style="margin-top: 2rem"><a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'htmlall':'UTF-8'}">{l s='Other payment methods' mod='cashway'}</a></p>
{/if}
