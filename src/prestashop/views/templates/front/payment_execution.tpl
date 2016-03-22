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
    <h3>{l s='Pay with CashWay ' mod='cashway'}
        <img src="{$this_path_cashway|escape:'urlpathinfo':'UTF-8'}/views/img/cashway-180x40.png"
            alt="{l s='CashWay' mod='cashway'}"
            width="180"
            height="40"
            style="margin: 0px 10px 5px 0px;" /></h3>

    {if $kyc_conditions.may_pay_this eq 'no'}
        <p>{l s='You have gone passed the maximum number of cash payments via CashWay over the last 12 months' mod='cashway'}
           <a href="https://help.cashway.fr/?q=depassement" id="cashway-link-more-info">{l s='more info' mod='cashway'}</a>).</p>
    {else}
        {if $available.0}
            <form action="{$link->getModuleLink('cashway', 'validation', [], true)|escape:'htmlall':'UTF-8'}"
                method="post">
                <ul>
                    <li><p>
                        {l s='After your order\'s confirmation,' mod='cashway'}
                        {l s='you will receive a SMS with your code' mod='cashway'}
                        {l s='to show to a newsagent' mod='cashway'}
                        {l s='present on the map below&nbsp;;' mod='cashway'}
                        {l s='whom will be able to validate and cash your payment.' mod='cashway'}
                        {l s='Your order will then be immediately processed.' mod='cashway'}
                    </p></li>
                    <li><table border="0">
                        <tr>
                            <td>{l s='Order total:' mod='cashway'}</td>
                            <td style="text-align: right;"><span id="amount" class="price">{displayPrice price=$total}</span></td>
                            <td>{if $use_taxes == 1}({l s='taxes included' mod='cashway'}){/if}</td>
                        </tr>
                        {if $cart_fee > 0}
                        <tr>
                            <td>{l s='Service fees:' mod='cashway'}</td>
                            <td style="text-align: right;"> {displayPrice price=$cart_fee}</td>
                        </tr>
                        {/if}
                        <tr>
                            <td><strong>{l s='Total amount to pay:' mod='cashway'}</strong></td>
                            <td style="text-align: right;"><strong><span id="amount" class="price">{displayPrice price=$total + $cart_fee}</span></strong></td>
                        </tr>
                    </table>
                    </li>
                    {if $kyc_conditions.may_pay_this eq 'req_kyc'}
                    <li><p>
                        <strong>{l s='Caution&nbsp;:' mod='cashway'}</strong>
                        {l s='in order to cash this amount,' mod='cashway'}
                        {l s='French regulations require us to control your identity.' mod='cashway'}
                        {l s='We will need&nbsp;:' mod='cashway'}
                        {l s='1) a recto/verso duplicate of your ID card,' mod='cashway'}
                        {l s='2) a proof of address of 3 months at most.' mod='cashway'}
                    </p></li>
                    {/if}
                </ul>
                <p><b>{l s='Please confirm your order by clicking \'I confirm my order\'.' mod='cashway'}</b>
                    {l s='By confirming your order, you confirm you have also read and agreed to' mod='cashway'}
                    <a href="https://help.cashway.fr/cgu/"
                       target="blank"
                       rel="nofollow"
                       style="color: orange;">
                    {l s='CashWay\'s terms and conditions' mod='cashway'}</a>.</p>

                <p class="cart_navigation" id="cart_navigation">
                    <input type="hidden" name="btn" value="button"/>
                    <input id="cashway-confirm-btn"
                        type="submit"
                        class="btn btn-primary button button-primary"
                        value="{l s='I confirm my order' mod='cashway'}"/>
                </p>
            </form>
            <img src="{$cashway_api_base_url|escape:'htmlall':'UTF-8'}/n/pu/considered?p=ps&amp;v=ok" alt="" />
        {else}
            <p><strong>{l s='Unfortunately&nbsp;: this payment method is temporarily unavailable' mod='cashway'}</strong>.
                {l s='We are doing our best to make it up and running as soon as possible.' mod='cashway'}
                <span>{$available.1|escape:'htmlall':'UTF-8'}</span></p>
            <p><a class="exclusive_large" href="/index.php?controller=order&step=3">{l s='You can choose another method' mod='cashway'}</a></p>
            <br>
            <img src="{$cashway_api_base_url|escape:'htmlall':'UTF-8'}/n/pu/considered?p=ps&amp;v=failed&amp;r={$available.2|escape:'htmlall':'UTF-8'}" alt="" />
        {/if}
    {/if}
    <br>
    <h4 id="cashway-map-l">{l s='Dealers near you&nbsp;:' mod='cashway'}</h4>
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
