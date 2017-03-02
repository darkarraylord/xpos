function clearInput() {
    jQuery('input[name="payment[cc_owner]"]').val('');
    jQuery('input[name="payment[cc_type]"]').val('');
    jQuery('input[name="payment[cc_number]"]').val('');
    jQuery('input[name="payment[cc_number]"]').next().val('');
    jQuery('input[name="payment[cc_exp_month]"]').val('');
    jQuery('input[name="payment[cc_exp_year]"]').val('');
}

function checkShippingInput() {
    if (jQuery('#customer_type').val() == 'exist') {
        var message ='';
        var errorShip = false;
        var errorBill = false;
        var billPhone = jQuery('#order-billing_address_address_telephone').val().toString().replace(/ /g,'');
        var shipPhone = jQuery('#order-shipping_address_address_telephone').val().toString().replace(/ /g,'');
        if (
            jQuery('#order-shipping_address_address_firstname').val() == ''
            || jQuery('#order-shipping_address_address_lastname').val() == ''
            || jQuery('#order-shipping_address_address_street0').val() == ''
            || jQuery('#order-shipping_address_address_city').val() == ''
            || jQuery('#order-billing_address_address_region_id').val() == ''
            || jQuery('#order-shipping_address_address_country_id').val() == ''
            || jQuery('#order-shipping_address_address_postcode').val() == ''
            || shipPhone == ''
        ) {
            errorShip = true;
            message = 'Missing Shipping address';
        }
        if (
            jQuery('#order-billing_address_address_firstname').val() == ''
            || jQuery('#order-billing_address_address_lastname').val() == ''
            || jQuery('#order-billing_address_address_street0').val() == ''
            || jQuery('#order-billing_address_address_city').val() == ''
            || jQuery('#order-billing_address_address_country_id').val() == ''
            || jQuery('#order-billing_address_address_region_id').val() == ''
            || jQuery('#order-billing_address_address_postcode').val() == ''
            || billPhone == ''
        ) {
            errorBill = true;
            message = 'Missing Billing address';

        }
        if(errorBill && errorShip){
            alert('Missing address of customer!');
            return false;
        }
        if(errorBill || errorShip){
            alert(message);
            return false;
        }

        var validatePhoneTypeMix = function (inputtxt) {
            var phoneRegEx1 = /^([\(]{1}[0-9]{3}[\)]{1}[ |\-]{0,1}|^[0-9]{3}[\-| ])?[0-9]{3}(\-| ){1}[0-9]{4}$/;
            var phoneRegEx2 = /(\s*\(?0\d{4}\)?\s*\d{6}\s*)|(\s*\(?0\d{3}\)?\s*\d{3}\s*\d{4}\s*)/;
            var phoneRegEx3 = /^(\+[1-9][0-9]*(\([0-9]*\)|-[0-9]*-))?[0]?[1-9][0-9\- ]*$/;
            var phoneRegEx4 = /^\D?(\d{3})\D?\D?(\d{3})\D?(\d{4})$/;
            var phoneRegEx5 = /^([0-9]( |-)?)?(\(?[0-9]{3}\)?|[0-9]{3})( |-)?([0-9]{3}( |-)?[0-9]{4}|[a-zA-Z0-9]{7})$/;
            var phoneRegEx6 = /((\(\d{3}\)?)|(\d{3}))([\s-./]?)(\d{3})([\s-./]?)(\d{4})/;
            var phoneRegEx7 = /^(\(?\+?[0-9]*\)?)?[0-9_\- \(\)]*$/;
            var phoneRegEx8 = /^((\\+[1-9]{1,4}[ \\-]*)|(\\([0-9]{2,3}\\)[ \\-]*)|([0-9]{2,4})[ \\-]*)*?[0-9]{3,4}?[ \\-]*[0-9]{3,4}?$/;
            var phoneRegEx9 = /^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,3})|(\(?\d{2,3}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$/;

            if(
                inputtxt.match(phoneRegEx1)
                || inputtxt.match(phoneRegEx1)
                || inputtxt.match(phoneRegEx2)
                || inputtxt.match(phoneRegEx3)
                || inputtxt.match(phoneRegEx4)
                || inputtxt.match(phoneRegEx5)
                || inputtxt.match(phoneRegEx6)
                || inputtxt.match(phoneRegEx7)
                || inputtxt.match(phoneRegEx8)
                || inputtxt.match(phoneRegEx9)
            ) {
                return true;
            }
            else {
                return false;
            }
        };

        if(!validatePhoneTypeMix(billPhone)){
            alert("Wrong Billing Number Phone Format!");
            return false;
        }
        if(!validatePhoneTypeMix(shipPhone)){
            alert("Wrong Shipping Number Phone Format!");
            return false;
        }
    }
    return true;
}

function show_customer_area() {
    enableBilling(jQuery("#order-billing_same_as_billing"));
    jQuery("#customer_search").hide();
    var customerArea = jQuery("#customer_area");
    customerArea.toggle();
    if(customerArea.is(":visible")){
        jQuery(".order-address").getNiceScroll().resize();
        jQuery(".order-address").getNiceScroll().show();
    }else{
        jQuery(".order-address").getNiceScroll().hide();

    }
}

function toFixed(value, precision) {
    var power = Math.pow(10, precision || 0);
    return Number(Math.round(value * power) / power);
}

function change_checkout_mode() {
    if(!checkShippingInput()){
        return;
    }
    $.jStorage.set('checkoutMode', true);
    firstCheckOrder = false;
    isCheckOrderAction = true;
    /*enable/disable guest Checkout*/
    var currentCustomer = jQuery('input[name="order[account][type]"]').val();
    if(currentCustomer == 'not_allow_guest') {
        alert("Guest can't check out! Please check in setting!");
        return;
    }
    /*end enable/disable guest Checkout */

    if (jQuery("#checkout_mode_button").hasClass("checkout_mode")) {
        if (isOnline()) {

            var discount_change = jQuery('#discount_change').val();
            var value = parseFloat(jQuery('#discount_value').val());
            var valueDisplay = parseFloat(jQuery('#discount_display').val());
            if ((value != valueDisplay) || discount_change == 1 || window.isDiscountPerItemMode == true ) {
                change_discount(valueDisplay);
                jQuery('#discount_change').val(0);
            }
            else {
                //var couponFlag = $F("coupons:code").length>0?true:false;
                //if((xposDiscount.getTotalDiscountPerItem() > 0) && couponFlag){
                //    order.itemsUpdateCoupon($F("coupons:code"));
                //}else{
                    order.itemsUpdate();
                //}
            }
//            var discount_code = jQuery('#discount_hidden').val();
//            if(discount_code != null && discount_code != "" ){
//                order.itemsUpdateCoupon(discount_code);
//                jQuery('#discount_hidden').val("");
//            }
//            else{
//                order.itemsUpdate();
//            }

        }else{

            if(window.enableOffline  == '0'){
                alert('Checkout offline mode has been disabled');
                return;
            }
            console.log('subtotal is Exclude Tax = ' + subTotalsExcludeTax);
            if(subTotalsExcludeTax  == 1 ){
                /* fix grand total*/
                var subTotal = 0;
                var tax = 0;
                var discount = 0;
                subTotal = jQuery('#subtotal_value').html();
                subTotal = unFormatCurrency(subTotal,priceFormat);
                tax = jQuery('#tax_value').html();
                tax = unFormatCurrency(tax,priceFormat);
                discount = jQuery('#discount_value').val();
                discount = unFormatCurrency(discount,priceFormat);

                var grandTotal = parseFloat(subTotal) + parseFloat(tax) - Math.abs(parseFloat(discount));
                var result = formatCurrency(grandTotal,priceFormat);

                jQuery('#grandtotal').html(result);
                jQuery('#cash-in').val(result);
                //end fix grand total
            }else{
                var subTotal = 0;
                var tax = 0;
                var discount = 0;
                subTotal = jQuery('#subtotal_value').html();
                subTotal = unFormatCurrency(subTotal,priceFormat);
                tax = jQuery('#tax_value').html();
                tax = unFormatCurrency(tax,priceFormat);
                discount = jQuery('#discount_value').val();
                discount = unFormatCurrency(discount,priceFormat);

                var grandTotal = parseFloat(subTotal) - Math.abs(parseFloat(discount));
                var result = formatCurrency(grandTotal,priceFormat);

                jQuery('#grandtotal').html(result);
                jQuery('#cash-in').val(result);
            }
        }
        jQuery("#checkout_mode_button").removeClass("checkout_mode");
        jQuery("#checkout_mode_button").addClass("button-checkout");

        jQuery("#overlay_left").show();

        jQuery("#center").hide();
        jQuery("#right").show();
        resizeTabbar();
    } else {
        jQuery("#search-box").focus();
        jQuery("#checkout_mode_button").removeClass("button-checkout");
        jQuery("#checkout_mode_button").addClass("checkout_mode");

        jQuery("#overlay_left").hide();

        jQuery("#right").hide();
        jQuery("#center").show();
        checkAndRemoveCoupon();
    }

}

function check_mode() {
    var mode = "checkout";
    if (jQuery("#checkout_mode_button").hasClass("checkout_mode")) {
        mode = "browser";
    }
    return mode;
}

function show_options_checkout() {
    // Check cashier permission
    var limit = jQuery('#is_user_limited').val();
    if (limit == 0) {
        jQuery('#invoice_toggle').css('display', 'none');
        jQuery('#shipment_toggle').css('display', 'none');
    }
    jQuery(".option-panel").toggle();
    if (!jQuery("#options_checkout_button").hasClass("active")) {
        var limit = jQuery('#is_user_limited').val();
        jQuery("#options_checkout_button").addClass("active");
        if (limit == 0) {
            jQuery('.invoice_option').css('display', 'none');
            jQuery('.shipment_option').css('display', 'none');
        }
    } else {
        jQuery("#options_checkout_button").removeClass("active");
    }
}

function isAvailability() {
    return jQuery('#network-status').val() == 'online';
}

function isOnline() {
    return jQuery('#network-availability').hasClass('network-online')
}

function IsPopupBlocker() {
    var oWin = window.open("", "testpopupblocker", "width=100,height=50,top=5000,left=5000");
    if (oWin == null || typeof(oWin) == "undefined") {
        return true;
    } else {
        oWin.close();
        return false;
    }
}

if (IsPopupBlocker()) {
    alert('Please disable the popup blocker in your browser to be able to print receipt.');
}

function setCheckBox(id) {
    var objId = '#' + id;
    var option_type = "";
    switch (id) {
        case "invoice_toggle":
            option_type = "doinvoice";
            break;
        case "shipment_toggle":
            option_type = "doshipment";
            break;
        case "receipt_toggle":
            option_type = "doprintreceipt";
            break;
        case "gift_receipt_toggle":
            option_type = "doprintgiftreceipt";
            break;
        case "mail_toggle":
            option_type = "doemailreceipt";
            break;
        default :
            break;
    }

    if (jQuery(objId).hasClass('active')) {
        jQuery(objId).removeClass('active');
        jQuery('#' + option_type).val(0);
    } else {
        jQuery(objId).addClass('active');
        jQuery('#' + option_type).val(1);
        /*TODO: add new option show gift receipt - view jira: JLNXC-4*/
        if (id == 'gift_receipt_toggle') {
            jQuery('#receipt_toggle').removeClass('active');
            jQuery('#' + 'doprintreceipt').val(0);
        } else {
            if (id == 'receipt_toggle') {
                jQuery('#gift_receipt_toggle').removeClass('active');
                jQuery('#' + 'doprintgiftreceipt').val(0);
            }
        }
    }
}

function show_checkout_area(area) {
    switch (area) {
        case "payment":
            jQuery(".checkout_area").hide();
            jQuery(".checkout_tab_button").removeClass('active');
            jQuery("#payment_tab_button").addClass('active');
            jQuery("#billing_method_area").show();
            break;
        case "shipping":
            jQuery(".checkout_area").hide();
            jQuery(".checkout_tab_button").removeClass('active');
            jQuery("#shipping_tab_button").addClass('active');
            jQuery("#shipping_method_area").show();
            break;
        case "discount":
            if(!isOnline()){
                alert('Coupon was disabled!');
                return;
            }
            jQuery(".checkout_area").hide();
            jQuery(".checkout_tab_button").removeClass('active');
            jQuery("#discount_tab_button").addClass('active');
            jQuery("#coupon_area").show();
            break;
        default :
            break;
    }
}

function show_customer_search() {
    jQuery(".order-address").getNiceScroll().hide();
    if(jQuery('#customer_options').hasClass('disabled')){
        jQuery("#customer_area").hide();
        return;
    }
    jQuery("#customer_options").toggleClass('active');
    jQuery("#customer_area").hide();
    jQuery("#customer_search").toggle();
}

function hide_message() {
    jQuery('#order-message').hide();
}

function initScroll(area) {
    jQuery(area).niceScroll();
    if (area == "#items_area") {
        jQuery(area).attr("style", "");
        jQuery(area).attr("style", "overflow: hidden;");
    }
}

function show_menu_item_sidebar(offline_mode) {
    if (offline_mode) {
        jQuery('#sidebar_menu_items li').hide();
        jQuery('#manual_reload_button').hide();
    } else {
        jQuery('#sidebar_menu_items li').show();
        jQuery('#manual_reload_button').hide();
        if (jQuery("#customer_type").val() != 'guest') {
            jQuery('.list-customer').removeClass('disabled');
            var limit = jQuery('#is_user_limited').val();
            if (limit == 0) jQuery('.list-customer').addClass('disabled');
        }
    }
}

function manual_reload() {
    $.jStorage.flush();
    location.reload(true);
}

function auto_select_field() {
    jQuery('.item-price, .item-qty, input[name="coupon_code"]').click(
        function () {
            jQuery(this).select();
            createSelection(0, 10, this);
        }
    );
}

function addCommas(nStr) {
    nStr += '';
    var x = nStr.split('.');
    var x1 = x[0];
    var x2 = x.length > 1 ? '.' + x[1] : '';
    if (x2 == '') {
        x2 = '.00';
    }
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}

//move from payment.phtml
function select_payment_method(payment_code) {
    //jQuery("#payment_method_hidden").val(payment_code);
    //jQuery("#payment_method_hidden").removeAttr('disabled');
    updateTotals();
    show_payment_block(payment_code);
    swipeNow(payment_code);
    resizeTabbar();
    //window.payment_method_code = payment_code
}

function select_payment_method_offline(payment_code,span){
    jQuery("#payment_method_hidden").val(payment_code);
    jQuery('.payment_offline').removeClass('active');
    var id = '#p_method_' + payment_code;
    jQuery(id).addClass('active');
    $jQuery('#payment_detail').html(jQuery(span).html());
    //jQuery("#payment_method_hidden").removeAttr('disabled');
    show_payment_block(payment_code);
    resizeTabbar();
    window.payment_method_code = payment_code
}

function show_payment_block(payment_code) {
    switch (payment_code) {
        case "ccsave":
            jQuery(".ccsave-block").show();
            break;
    }
    if(payment_code != 'ccsave'){
        jQuery(".ccsave-block").hide();
    }
    jQuery("#method_area").getNiceScroll().resize();
}

function resizeTabbar() {
    jQuery('#payment_tab_button').css('height', 'auto');
    var max = Math.max.apply(Math, jQuery(".checkout_tab_button").map(
        function () {
            return jQuery(this).height();
        }
    ));
    jQuery("#checkout_method_bar").each(function () {
        jQuery(this).find(".checkout_tab_button").height(max);
    });
}

function validateCreditCar() {
    var paymentMethod = window.payment_method_code;
    if (typeof paymentMethod != 'undefined') {
        paymentMethod = window.payment_method_code;
    } else {
        paymentMethod = jQuery("#payment_method_hidden").val();
    }

    var formName = 'payment_form_' + paymentMethod;
    paymentMethod = '#payment_form_' + paymentMethod;
    var flag = jQuery(paymentMethod).val();
    if (flag == '') {
        if(jQuery("#payment_method_hidden").val() == 'authorizenet'){
            return true;
        }
        var valid5, result5;
        valid5 = new Validation(formName, {onSubmit: false});
        result5 = valid5.validate();
        if(!result5){
            alert("Invalid Payment!!!");
        }
        return result5;
    }

    return true;
}

jQuery(document).ready(function () {
    jQuery("#messages").html('');

    jQuery('#search-box').on('keypress', function (event) {
        if (event.which == '13') {
            //createSelection(0, 99, this);
        }
    });

    jQuery("#search-box").focus(function () {
        var mode = check_mode();
        if (mode == 'checkout') {
            jQuery("#checkout_mode_button").removeClass("button-checkout");
            jQuery("#checkout_mode_button").addClass("checkout_mode");
            jQuery("#overlay_left").hide();
            jQuery("#right").hide();
            jQuery("#center").show();
            checkAndRemoveCoupon();
        }
    });

    if (jQuery('#keyboard_shortcut').val() == 1) {
        Mousetrap.bind(['o'], function (e) {
            if (!orderListLoaded) {
                showOrderList();
            } else {
                orderListLoaded = false;
                jQuery('.popup-list-order').bPopup().close();
            }
        });
        Mousetrap.bind(['c'], function (e) {
            cancelOrder();
        });
        Mousetrap.bind(['m'], function (e) {
            change_checkout_mode();
        });
        Mousetrap.bind(['q'], function (e) {
            xpos_user_logout();
        });
        Mousetrap.bind(['t'], function (e) {
            if (!transactionMoneyLoaded) {
                showTransactionList();
            } else {
                transactionMoneyLoaded = false;
                jQuery('.popup-list-transaction').bPopup().close();
            }
        });
        Mousetrap.bind(['p'], function (e) {
            if (check_mode() == 'browser') {
                change_checkout_mode();
            }
            show_checkout_area('payment')
        });
        Mousetrap.bind(['s'], function (e) {
            if (check_mode() == 'browser') {
                change_checkout_mode();
            }
            show_checkout_area('shipping')
        });
        Mousetrap.bind(['d'], function (e) {
            if (check_mode() == 'browser') {
                change_checkout_mode();
            }
            show_checkout_area('discount')
        });
        Mousetrap.bind(['enter'], function (e) {
            if (check_mode() == 'checkout') {
                if(!jQuery('#checkout_popup').is(':visible')){
                    xpos_checkout(checkoutcomfirm);
                    var checkoutcomfirm = jQuery('#checkoutcomfirm').hide();
                }else{
                    checkoutConfirm();
                }
            }
        });
        Mousetrap.bind('up up down down left right left right b a enter', function () {
            alert('X-POS v3.3');
        });
    }
});
function absValue(value){
    return Math.abs(Number(unFormatCurrency(value,priceFormat)).toFixed(2));
}
function checkAndRemoveCoupon(){
    var couponFlag = $F("coupons:code").length > 0 ? true : false;
    if(couponFlag && isOnline()){
        order.applyCoupon('');
    }
}
function removeCacheCoupon(){
    window.COUPON_CODE = false;
}