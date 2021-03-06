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
    <h3>{l s='Payer en espèces avec ' mod='cashway'}
        <img src="{$this_path_cashway|escape:'urlpathinfo':'UTF-8'}/views/img/cashway-180x40.png"
            alt="{l s='CashWay' mod='cashway'}"
            width="180"
            height="40"
            style="margin: 0px 10px 5px 0px;" /></h3>

    {if $kyc_conditions.may_pay_this eq 'no'}
        <p>Hélas, vous avez dépassé le montant maximum possible d'achats
            via CashWay sur la période des 12 derniers mois
            (<a href="https://help.cashway.fr/?q=depassement" id="cashway-link-more-info">plus d&rsquo;informations</a>).</p>
    {else}
        {if $available.0}
            <form action="{$link->getModuleLink('cashway', 'validation', [], true)|escape:'htmlall':'UTF-8'}"
                method="post">
                <ul>
                    <li><p>
                        Après confirmation de votre commande,
                        vous recevrez par email et par SMS un code
                        à présenter chez un des buralistes
                        présents sur la carte ci-dessous&nbsp;;
                        celui pourra ainsi encaisser et valider votre paiement.
                        Votre commande sera alors immédiatement traitée par nos services.
                    </p></li>
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
                        <strong>Total à payer au buraliste&nbsp;:
                        <span id="amount" class="price">{displayPrice price=$total + $cart_fee}</span>.</strong>
                    </p></li>
                    {if $kyc_conditions.may_pay_this eq 'req_kyc'}
                    <li><p>
                        <strong>Attention&nbsp;:</strong>
                        pour encaisser ce montant,
                        la réglementation française nous impose de contrôler votre identité.
                        Nous aurons ainsi besoin&nbsp;:
                        1) d&rsquo;une copie recto/verso de votre carte d&rsquo;identité,
                        2) d&rsquo;un justificatif de domicile de moins de 3 mois.
                    </p></li>
                    {/if}
                </ul>
                <p><b>{l s='Please confirm your order by clicking \'I confirm my order\'.' mod='cashway'}</b></p>
                <div class="form-group form-group-sm">
                    <p>En confirmant votre commande, vous reconnaissez avoir lu et adhéré sans réserve
                        aux <a href="https://help.cashway.fr/cgu/"
                               target="blank"
                               rel="nofollow"
                               style="color: orange;">
                        conditions générales de CashWay</a>.</p>
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
            </form>
            <img src="{$cashway_api_base_url|escape:'htmlall':'UTF-8'}/n/pu/considered?p=ps&amp;v=ok" alt="" />
        {else}
            <p><strong>Hélas&nbsp;: cette méthode de paiement est temporairement indisponible</strong>.
                Nous mettons tout en œuvre pour la rétablir le plus tôt possible.
                <span>{$available.1|escape:'htmlall':'UTF-8'}</span></p>
            <p><a class="exclusive_large" href="/index.php?controller=order&step=3">Vous pouvez choisir une autre méthode</a></p>
            <br>
            <img src="{$cashway_api_base_url|escape:'htmlall':'UTF-8'}/n/pu/considered?p=ps&amp;v=failed&amp;r={$available.2|escape:'htmlall':'UTF-8'}" alt="" />
        {/if}
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

    <p class="cart_navigation clearfix"
        style="margin-top: 2rem">
        <a class="button-exclusive btn btn-default"
            title="Précédent"
            href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'htmlall':'UTF-8'}">
                <i class="icon-chevron-left"></i> {l s='Other payment methods' mod='cashway'}</a></p>
{/if}
