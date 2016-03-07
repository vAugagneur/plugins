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
      Pour régler les <span class="price">{$total_to_pay|escape:'htmlall':'UTF-8'}
      {$cart_fee|escape:'htmlall':'UTF-8'}</span> de votre commande,
      rendez-vous dans un des points de paiement
      indiqués sur <a href="#cashway-map-l">notre carte</a>, muni du code suivant&nbsp;:
      <code id="cashway-barcode-label">{$barcode|escape:'htmlall':'UTF-8'|substr:7:15|wordwrap:3:' ':true}</code>
      (ce code n'est valide que jusqu'au {$expires_fr|escape:'htmlall':'UTF-8'}).
    </p>
    </p>
    {if $kyc_conditions.may_pay_this eq 'req_kyc'}
    <p>
      <strong>Attention&nbsp;:</strong>
      nous avons besoin&nbsp;:
      1) d&rsquo;une copie de votre carte d&rsquo;identité
      et 2) d&rsquo;un justificatif de domicile de moins de 3 mois,
      pour encaisser votre paiement.
      <strong>Sans réception et validation de ces documents, votre code ne sera pas actif.</strong>
      Vous pouvez nous envoyer ces documents scannés
      <a href="mailto:{$kyc_upload_mail|escape:'htmlall':'UTF-8'}?subject=Validation {$barcode|escape:'htmlall':'UTF-8'}" class="button button-small" id="cashway-kyc-email">par email</a>
      ou <a href="{$kyc_upload_url|escape:'htmlall':'UTF-8'}?barcode={$barcode|escape:'htmlall':'UTF-8'}" class="button button-small" id="cashway-kyc-form">par formulaire</a>
      (<a href="{$kyc_upload_url|escape:'htmlall':'UTF-8'}?barcode={$barcode|escape:'htmlall':'UTF-8'}">plus d&rsquo;informations).
    </p>
    {/if}
    <p><a href="https://api.cashway.fr/1/b/{$barcode|escape:'htmlall':'UTF-8'}.html?f=payment" class="button">Imprimer le ticket de paiement correspondant</a></p>

    <p>{l s='An email and a text message have been sent to you with all this information.' mod='cashway'}
        <br /><br /><strong>{l s='Your order will be sent as soon as we receive your payment.' mod='cashway'}</strong>
    </p>

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

    <p>
        {l s='For any questions or for further information, please contact our' mod='cashway'}
        <a href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='customer service department.' mod='cashway'}</a>.
        <img src="{$cashway_api_base_url|escape:'htmlall':'UTF-8'}/1/n/pu/returned?p=ps&amp;v=ok" alt="" />
    </p>

    <p>Vous pouvez consulter les
      <a href="https://help.cashway.fr/cgu/"
         target="blank"
         rel="nofollow">conditions générales de CashWay</a>.</p>


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
