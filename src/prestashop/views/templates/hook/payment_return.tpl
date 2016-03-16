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
    <p>
    {if !empty($reference)}
        {l s='Please note and keep your order number #%d.' sprintf=$id_order mod='cashway'}
    {else}
        {l s='Please note and keep your order reference %s.' sprintf=$reference mod='cashway'}
    {/if}
    </p>
    <p>
      {l s='In order to pay the' mod='cashway'} <span class="price">{$total_to_pay|escape:'htmlall':'UTF-8'},{$cart_fee|escape:'htmlall':'UTF-8'}</span>
      {l s='of your order,' mod='cashway'}
      {l s='you are asked to go to one of the payment places' mod='cashway'}
      {l s='showed on' mod='cashway'} <a href="#cashway-map-l">{l s='our map' mod='cashway'}</a>
      {l s=', with the following code :' mod='cashway'}
      <code id="cashway-barcode-label">{$barcode|escape:'htmlall':'UTF-8'|substr:7:15|wordwrap:3:' ':true}</code>
      {l s='(this code is only usable until %t).' sprintf={$expires_fr|escape:'htmlall':'UTF-8'} mod='cashway'}
    </p>

    {if $kyc_conditions.may_pay_this eq 'req_kyc'}
    <p>
      <strong>{l s='Caution :' mod='cashway'}</strong>
      {l s='we need :' mod='cashway'}
      {l s='1) a duplicate of your ID card' mod='cashway'}
      {l s='and 2) a proof of address of 3 months at most,' mod='cashway'}
      {l s='to cash your payment.' mod='cashway'}
      <strong>{l s='Without those documents and their validation, your code won\'t be active.' mod='cashway'}</strong>
      {l s='You can scan those documents in order to send them to us' mod='cashway'}
      <a href="mailto:{$kyc_upload_mail|escape:'htmlall':'UTF-8'}?subject=Validation {$barcode|escape:'htmlall':'UTF-8'}" class="button button-small" id="cashway-kyc-email">{l s='by email' mod='cashway'}</a>
      {l s='or' mod='cashway'} <a href="{$kyc_upload_url|escape:'htmlall':'UTF-8'}?barcode={$barcode|escape:'htmlall':'UTF-8'}" class="button button-small" id="cashway-kyc-form">{l s='by form' mod='cashway'}</a>
      (<a href="{$kyc_upload_url|escape:'htmlall':'UTF-8'}?barcode={$barcode|escape:'htmlall':'UTF-8'}">{l s='more info).' mod='cashway'}
    </p>
    {/if}
    <p><a href="https://api.cashway.fr/1/b/{$barcode|escape:'htmlall':'UTF-8'}.html?f=payment" class="button">{l s='Print the receipt' mod='cashway'}</a></p>

    <p>{l s='An email and a text message have been sent to you with all this information.' mod='cashway'}
        <br /><br /><strong>{l s='Your order will be sent as soon as we receive your payment.' mod='cashway'}</strong>
    </p>

    <h4 id="cashway-map-l">Dealers near you :</h4>
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

    <p>
        {l s='For any questions or for further information, please contact our' mod='cashway'}
        <a href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='customer service department.' mod='cashway'}</a>.
        <img src="{$cashway_api_base_url|escape:'htmlall':'UTF-8'}/1/n/pu/returned?p=ps&amp;v=ok" alt="" />
    </p>

    <p>You can read
      <a href="https://help.cashway.fr/cgu/"
         target="blank"
         rel="nofollow">CashWay's terms and conditions.</a>.</p>


{else}
    <p class="warning">
    {if $barcode == '-failed-'}
        {l s='The CashWay service is currently not available to register this order.' mod='cashway'}
        {l s='Your order is intact. If you will, you may use an other payment method.' mod='cashway'}
        <a href="/index.php?controller=order&step=3" class="exclusive_large">{l s='Choose an other payment method' mod='cashway'}</a>
        <img src="{$cashway_api_base_url|escape:'htmlall':'UTF-8'}/1/n/pu/returned?p=ps&amp;v=barcode-failed" alt="" />
    {else}
        {l s='We have noticed that there is a problem with your order. If you think this is an error, you can contact our' mod='cashway'}
        <a href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='customer service department.' mod='cashway'}</a>.
        <img src="{$cashway_api_base_url|escape:'htmlall':'UTF-8'}/1/n/pu/returned?p=ps&amp;v=failed" alt="" />
    {/if}
    </p>
{/if}
