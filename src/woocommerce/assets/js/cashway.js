jQuery('input[type="submit"]').click(function(e){
    e.preventDefault();
    // Check gateway is enable
    if( ! jQuery('#woocommerce_woocashway_enabled').is(':checked') ) {
      jQuery("#mainform").submit();
      return;
    }
    jQuery(window).block({
        message: CashWayJSParams.checking,
        baseZ: 99999,
        overlayCSS:
        {
            background: "#fff",
            opacity: 0.6
        },
        css: {
            padding:        "20px",
            zindex:         "9999999",
            textAlign:      "center",
            color:          "#555",
            border:         "3px solid #aaa",
            backgroundColor:"#fff",
            cursor:         "wait",
            lineHeight:     "24px",
            fontWeight:     "bold",
            position:       "fixed"
        }
    });

    jQuery.post( CashWayJSParams.url, { login: jQuery("input[name='woocommerce_woocashway_cashway_login']").val(), password: jQuery("input[name='woocommerce_woocashway_cashway_password']").val()})
    .done(function(data) {
        if( data == 'ok' ) {
            jQuery("#mainform").submit();
        }else{
            jQuery(window).unblock();
            alert(CashWayJSParams.error_login);
        }
    })
    .fail(function() {
        jQuery(window).unblock();
        alert(CashWayJSParams.error_unknown);
    });

})
