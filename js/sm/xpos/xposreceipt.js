/**
 * Created by NguyenCT on 5/5/14.
 */
var pageReceiptTemplate =
    '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">\
    <head>\
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>\
        <title>Invoice - Magento Admin</title>\
        <style>\
            @media (max-width: 480px) {\
                    .items-view table .row-total {\
                display: none !important;\
            }\
            }\
            @media (max-width: 380px) {\
            .items-view table .item-name {\
                    max-width: 120px;\
                }\
            .items-view table .price-container,\
            .items-view table .tax {\
                    display: none !important;\
                }\
            .items-view table .row-total {\
                    display: table-cell !important;\
                }\
            }\
            * {\
                box-sizing: border-box;\
            }\
            html {\
                font-family: {fontType};\
                font-size: 13px;\
                text-transform: uppercase;\
            }\
            body, h1, h2, h3, h4, h5, h6, ul {\
                padding: 0;\
                margin: 0;\
                font-weight: normal;\
            }\
            table {\
                border-spacing: 0;\
            }\
            ul {\
                list-style: none;\
            }\
            .flex-container {\
                display: -webkit-flex;\
                display: flex;\
                -webkit-justify-content: space-around;\
                justify-content: space-around;\
            }\
            .paper {\
                margin: 0;\
                padding: 15px 0;\
            }\
            .logo-container, .store-info {\
                text-align: center;\
            }\
            .store-info {\
                margin-bottom: 7px;\
            }\
            .store-info .name {\
                display: -webkit-flex;\
                display: flex;\
                font-weight: 800;\
                -webkit-justify-content: space-between;\
                justify-content: space-between;\
                -webkit-align-items: center;\
                align-items: center;\
            }\
            #logo {\
                font-family: Segoe UI, Arial, sans-serif;\
                font-weight: 800;\
                font-size: 32px;\
            }\
            #logo img {\
                max-width: 200px;\
            }\
            .receipt-info {\
                display: -webkit-flex;\
                display: flex;\
                -webkit-justify-content: space-between;\
                justify-content: space-between;\
                margin-bottom: 7px;\
            }\
            .receipt-info .customer-info {\
                text-transform: uppercase;\
            }\
            .items-view {\
                margin-bottom: 7px;\
                padding: 3px 0;\
            }\
            .items-view table {\
                width: 100%;\
                text-align: right;\
                font-size: inherit;\
                font-weight: inherit;\
                border-collapse: separate;\
            }\
            .items-view table thead {\
                vertical-align: top;\
            }\
            .items-view table tbody {\
                vertical-align: top;\
            }\
            .items-view table tbody tr td {\
                padding: 5px 0;\
            }\
            .items-view table td,\
            .items-view table th {\
                border-left: 10px solid transparent;\
                text-transform: uppercase;\
            }\
            .items-view table td:first-child,\
            .items-view table th:first-child {\
                border-left: none;\
            }\
            .items-view table .item-name {\
                overflow: hidden;\
                white-space: normal;\
                text-transform: capitalize;\
            }\
            .items-view table .item-name .sku {\
                display: block;\
                text-transform: none;\
            }\
            .items-view table td[data-metadata="qty"] {\
                font-weight: 800;\
            }\
            .total-zone {\
                margin-bottom: 7px;\
                text-transform: uppercase;\
            }\
            .total-zone .title {\
                display: -webkit-flex;\
                display: flex;\
                font-weight: 800;\
                -webkit-justify-content: space-between;\
                justify-content: space-between;\
                -webkit-align-items: center;\
                align-items: center;\
            }\
            .total-zone .title .literal {\
            }\
            .total-zone table {\
                font-weight: inherit;\
                font-size: inherit;\
                width: 100%;\
            }\
            .methods {\
                text-align: right;\
                margin-bottom: 15px;\
            }\
            .footer {\
                text-align: center;\
            }\
            .footer .highlight {\
                text-transform: uppercase;\
                font-weight: 400;\
                font-size: 28px;\
            }\
            .footer .separate {\
                margin-bottom: 15px;\
            }\
            .date {\
                text-align: right;\
            }\
            @media screen {\
            .paper {\
                    margin: 15px auto;\
                    max-width: 600px;\
                }\
            }\
            @media print {\
            }\
        </style>\
    </head>\
    <body>\
        <div class="paper" style="max-width:">\
            <div class="logo-container" style="text-align:{logoHtmlAlignment};">\
                <div id="logo" class="logo">\
                    {logo}\
                </div>\
            </div>\
            <div class="store-info" style="text-align:{storeInfoHtmlAlignment};">\
                <div class="slogan">{sloganLiteral}</div>\
                {store_information}\
            </div>\
            \
            <div class="receipt-info">\
                <div class=\'container\'>\
                    <div class="customer-info">\
                        <span>{customer_label}</span>\
                        <span><strong>{customer_name} #{customer_id} </strong></span>\
                    </div>\
                    <div class="order">\
                        <span>{order_label} #</span>\
                        <span><strong>{order_no}</strong></span>\
                    </div>\
                    <div class="cashier">\
                        <span>{cashier_label}</span>\
                        <span>{cashier_name}</span>\
                    </div>\
                </div>\
                <div class="date">\
                    <div class="day">{order_date}</div>\
                    <div class="time">{order_time}</div>\
                </div>\
            </div>\
            <div class="methods" style="text-align:{methodsHtmlAlignment}">\
                <div class="shipping">Pickup Shipping - X-POS Shipping</div>\
                <div class="payment">{payment}</div>\
            </div>\
            \
            <div class="items-view" style="{htmlSeparatorStyle}">\
                <table>\
                    <thead>\
                        <tr>\
                            <th class="item-name" data-metadata="item-name" align="left">{item_label}</th>\
                            <th data-metadata="qty">QTY</th>\
                            <th class="price-container" data-metadata="price">{price_label}</th>\
                            <th class="tax" data-metadata="tax-amount">{tax_label}</th>\
                            <th class="row-total" data-metadata="row-total" align="right">{subtotal_label}</th>\
                        </tr>\
                    </thead>\
                    <tbody>\
                        {order_items}\
                    </tbody>\
                </table>\
                </div>\
                <div class="total-zone">\
                    <table>\
                        <tbody>\
                            {subtotalContainer}\
                            {discountContainer}\
                            {taxContainer}\
                            <tr>\
                                <td>{total_paid_label}</td>\
                                <td align="right"><strong>{total_paid}</strong></td>\
                            </tr>\
                            <tr>\
                                <td>{total_label}</td>\
                                <td align="right"><strong>{grand_total}</strong></td>\
                            </tr>\
                        </tbody>\
                    </table>\
                </div>\
            <div class="footer">\
                <div class="">----------***----------</div>\
                <div id="fore">{footerMessage}</div>\
            </div>\
        </div>\
    </body>\
    </html>\
    ';
var orderItemTemplate =
    '<tr>\
        <td class="item-name" data-metadata="item-name" align="left">\
            {name}\
            <span class="sku">{sku}</span>\
        </td>\
        <td data-metadata="qty">{qty}</td>\
        <td class="price-container" data-metadata="price">{price}</td>\
        <td class="tax" data-metadata="tax-amount">{tax}</td>\
        <td class="row-total" data-metadata="row-total" align="right">{subtotal}</td>\
    </tr>';

var sumUpTemplate =
    '<tr>\
        <td>{title}</td>\
        <td align="right"><strong>{value}</strong></td>\
    </tr>';
function printReceipt(order){
    var taxSalesDisplayPrice = document.getElementById('tax_sales_display_price').value;
    var taxSalesDisplaySubtotal = document.getElementById('tax_sales_display_subtotal').value;

    var itemNo = 1;
    var subtotal = parseFloat(0);
    var subtotalInclTax = parseFloat(0);

    var sortedOrder = sortObject(order);
    var orderItemsOutput = '';
    jQuery.each(sortedOrder, function (i, orderItem) {
        //calc
        orderItem.subtotal = orderItem.price * orderItem.qty;
        orderItem.subtotalInclTax = orderItem.subtotal * (1 + orderItem.tax / 100);
        var tax = orderItem.subtotalInclTax - orderItem.subtotal;
        var orderOption = '';
        if (orderItem.options.length > 0) {
            jQuery.each(orderItem.options, function (i, option) {
//                var optionName = option.name.replace('[', '][');
                //optionInput+='<span class="option-title">'+option.optionTitle+': </span><span class="option-name">'+option.qty+'x '+option.title+'</span></li>';
                //orderOption += optionInput;
            });
        }

        //orderOption+='</ul>';
        var tempOrderItem = {};
        if (orderItem.qty == 0) {
            tempOrderItem.style = 'style="display:none"';
        }

        tempOrderItem.no = itemNo;
        tempOrderItem.id = orderItem.id;
        tempOrderItem.name = orderItem.name;
        tempOrderItem.sku = orderItem.sku;
        tempOrderItem.qty = orderItem.qty;
        tempOrderItem.tax = formatCurrency(tax,window.multiStoreView.priceFormat);
        tempOrderItem.price = formatCurrency((taxSalesDisplayPrice == 1) ? orderItem.price : orderItem.price * (1 + orderItem.tax / 100), window.multiStoreView.priceFormat);
        tempOrderItem.subtotal = formatCurrency((taxSalesDisplaySubtotal == 1) ? orderItem.subtotal : orderItem.subtotalInclTax, window.multiStoreView.priceFormat);
        orderItemsOutput += nano(orderItemTemplate, tempOrderItem);
        itemNo++;
        subtotal += parseFloat(orderItem.subtotal);
        subtotalInclTax += parseFloat(orderItem.subtotalInclTax);
    });
    var tax = subtotalInclTax - subtotal;
    var discount =  jQuery('#discount_hidden').val();
    if(discount == ''){
        discount = 0;
    }
    subtotalInclTax = subtotalInclTax - parseFloat(discount);
    var orderReceipt = {};
    //label

    orderReceipt.customer_label = customer_label;
    orderReceipt.order_label = order_label;
    orderReceipt.tax_label = tax_label;
    orderReceipt.taxes_label = taxes_label;
    orderReceipt.subtotal_label = subtotal_label;
    orderReceipt.price_label = price_label;
    orderReceipt.item_label = item_label;
    orderReceipt.total_label = total_label;
    orderReceipt.total_paid_label = total_paid_label;
    orderReceipt.qty_label = qty_label;

    orderReceipt.store_information = xposConfig.storeInfo;
    orderReceipt.order_no = 'N/A';
    orderReceipt.order_date = getCurrentDate();
    orderReceipt.customer_name = jQuery('#customer_name').text();
    orderReceipt.customer_id = jQuery('#order_customer_id').val();
    orderReceipt.order_time = getCurrentTime();
    orderReceipt.cashier_name;
    if($.jStorage.get('xpos_user') != null){
        orderReceipt.cashier_name = jQuery('#cashier_name').text();
    }
    orderReceipt.payment = jQuery('#payment_detail').html();
    orderReceipt.order_items = orderItemsOutput;
    orderReceipt.subtotal = formatCurrency(xConifg.subtotalDisplayInclTax() ? subtotalInclTax : subtotal, window.multiStoreView.priceFormat);
    orderReceipt.tax = formatCurrency(tax, window.multiStoreView.priceFormat);
    orderReceipt.discount = formatCurrency(discount, window.multiStoreView.priceFormat);
    orderReceipt.grand_total = formatCurrency(subtotalInclTax, window.multiStoreView.priceFormat);
    orderReceipt.total_paid = formatCurrency(subtotalInclTax, window.multiStoreView.priceFormat);
    orderReceipt.skin_url = xposConfig.skinUrl;

    orderReceipt.taxContainer = '';
    orderReceipt.discountContainer = '';
    orderReceipt.subtotalContainer = '';

    orderReceipt.fontType = document.getElementById('xpos_receipt_font_type').value;
    orderReceipt.logoLiteral = document.getElementById('xpos_receipt_logo_literal').value;
    orderReceipt.sloganLiteral = document.getElementById('xpos_receipt_slogan_literal').value;
    orderReceipt.footerMessage = document.getElementById('xpos_receipt_fore_message').value;
    orderReceipt.logoHtmlAlignment = document.getElementById('xpos_receipt_logo_html_alignment').value;
    orderReceipt.storeInfoHtmlAlignment = document.getElementById('xpos_receipt_store_info_html_alignment').value;
    orderReceipt.methodsHtmlAlignment = document.getElementById('xpos_receipt_methods_html_alignment').value;

    if (document.getElementById('xpos_receipt_logo_image_file').value != '') {
        orderReceipt.logo = '<img src="'+ document.getElementById('xpos_receipt_logo_image_file').value +'" />';
    } else {
        orderReceipt.logo = '<span>'+ document.getElementById('xpos_receipt_logo_literal').value +'</span>';
    }

    switch (document.getElementById('xpos_receipt_info_separator').value) {
        case 'line':
            orderReceipt.htmlSeparatorStyle = 'border-top: solid 1px #000; border-bottom: solid 1px #000';
            break;
        case 'dash':
            orderReceipt.htmlSeparatorStyle = 'border-top: dashed 1px #000; border-bottom: dashed 1px #000';
            break;
        default:
            orderReceipt.htmlSeparatorStyle = '';
            break;
    }

    var additionalFields = document.getElementById('xpos_receipt_addition_fields_to_display').value.split(',');
    additionalFields.forEach(function(value, index) {
        switch (value) {
            case 'tax':
                orderReceipt.taxContainer = nano(sumUpTemplate, {'title':taxes_label, 'value':orderReceipt.tax});
                break;
            case 'discount':
                orderReceipt.discountContainer = nano(sumUpTemplate, {'title':discount_label, 'value':orderReceipt.discount});
                break;
            case 'subtotal':
                orderReceipt.subtotalContainer = nano(sumUpTemplate, {'title':subtotal_label, 'value':orderReceipt.subtotal});
                break;
        }
    });

    var data = nano(pageReceiptTemplate,orderReceipt);
    var receipt = window.open('', '', 'width=900,height=600,resizable=1,scrollbars=1');
    receipt.document.write(data);
    /*
    *  bug browser go online though present is off
    * */
    goOffline();
    receipt.print();
    setTimeout(function() {receipt.close()}, 100);

}

function getCurrentDate(){
    var today = new Date();
    var dd = today.getDate();
    var mm = today.getMonth()+1; //January is 0!
    var yyyy = today.getFullYear();
    if(dd<10) {
        dd='0'+dd
    }
    if(mm<10) {
        mm='0'+mm
    }

    /*
     * Temporarily hack
     * TODO: rework
     */
    switch (document.getElementById('xpos_receipt_day_format').value) {
        case 'd/m/Y':
            return formatDate(today, 'd/MM/yyyy');
            break;
        case 'Y/m/d':
            return formatDate(today, 'yyyy/MM/d');
            break;
        case 'Y/d/m':
            return formatDate(today, 'yyyy/d/MM');
            break;
        case 'M d Y':
            return formatDate(today, 'MMM d yyyy');
            break;
        case 'M D Y':
            return formatDate(today, 'MMM ddd yyyy');
            break;
        case 'd M Y':
            return formatDate(today, 'd MMM yyyy');
            break;
        default:
            return formatDate(today, 'MM/d/yyyy');
    }
}

function getCurrentTime(){
    var today = new Date();
    var hh = today.getHours();
    var mm = today.getMinutes(); //January is 0!
    var ss = today.getSeconds();
    if(hh<10) {
        hh='0'+hh
    }
    if(mm<10) {
        mm='0'+mm
    }

    return hh+':'+mm+':'+ss;
}

/*
 * Credit: http://stackoverflow.com/questions/14638018/current-time-formatting-with-javascript#14638191
 */
function formatDate(date, format, utc) {
    var MMMM = ["\x00", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    var MMM = ["\x01", "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    var dddd = ["\x02", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    var ddd = ["\x03", "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];

    function ii(i, len) {
        var s = i + "";
        len = len || 2;
        while (s.length < len) s = "0" + s;
        return s;
    }

    var y = utc ? date.getUTCFullYear() : date.getFullYear();
    format = format.replace(/(^|[^\\])yyyy+/g, "$1" + y);
    format = format.replace(/(^|[^\\])yy/g, "$1" + y.toString().substr(2, 2));
    format = format.replace(/(^|[^\\])y/g, "$1" + y);

    var M = (utc ? date.getUTCMonth() : date.getMonth()) + 1;
    format = format.replace(/(^|[^\\])MMMM+/g, "$1" + MMMM[0]);
    format = format.replace(/(^|[^\\])MMM/g, "$1" + MMM[0]);
    format = format.replace(/(^|[^\\])MM/g, "$1" + ii(M));
    format = format.replace(/(^|[^\\])M/g, "$1" + M);

    var d = utc ? date.getUTCDate() : date.getDate();
    format = format.replace(/(^|[^\\])dddd+/g, "$1" + dddd[0]);
    format = format.replace(/(^|[^\\])ddd/g, "$1" + ddd[0]);
    format = format.replace(/(^|[^\\])dd/g, "$1" + ii(d));
    format = format.replace(/(^|[^\\])d/g, "$1" + d);

    var H = utc ? date.getUTCHours() : date.getHours();
    format = format.replace(/(^|[^\\])HH+/g, "$1" + ii(H));
    format = format.replace(/(^|[^\\])H/g, "$1" + H);

    var h = H > 12 ? H - 12 : H == 0 ? 12 : H;
    format = format.replace(/(^|[^\\])hh+/g, "$1" + ii(h));
    format = format.replace(/(^|[^\\])h/g, "$1" + h);

    var m = utc ? date.getUTCMinutes() : date.getMinutes();
    format = format.replace(/(^|[^\\])mm+/g, "$1" + ii(m));
    format = format.replace(/(^|[^\\])m/g, "$1" + m);

    var s = utc ? date.getUTCSeconds() : date.getSeconds();
    format = format.replace(/(^|[^\\])ss+/g, "$1" + ii(s));
    format = format.replace(/(^|[^\\])s/g, "$1" + s);

    var f = utc ? date.getUTCMilliseconds() : date.getMilliseconds();
    format = format.replace(/(^|[^\\])fff+/g, "$1" + ii(f, 3));
    f = Math.round(f / 10);
    format = format.replace(/(^|[^\\])ff/g, "$1" + ii(f));
    f = Math.round(f / 10);
    format = format.replace(/(^|[^\\])f/g, "$1" + f);

    var T = H < 12 ? "AM" : "PM";
    format = format.replace(/(^|[^\\])TT+/g, "$1" + T);
    format = format.replace(/(^|[^\\])T/g, "$1" + T.charAt(0));

    var t = T.toLowerCase();
    format = format.replace(/(^|[^\\])tt+/g, "$1" + t);
    format = format.replace(/(^|[^\\])t/g, "$1" + t.charAt(0));

    var tz = -date.getTimezoneOffset();
    var K = utc || !tz ? "Z" : tz > 0 ? "+" : "-";
    if (!utc) {
        tz = Math.abs(tz);
        var tzHrs = Math.floor(tz / 60);
        var tzMin = tz % 60;
        K += ii(tzHrs) + ":" + ii(tzMin);
    }
    format = format.replace(/(^|[^\\])K/g, "$1" + K);

    var day = (utc ? date.getUTCDay() : date.getDay()) + 1;
    format = format.replace(new RegExp(dddd[0], "g"), dddd[day]);
    format = format.replace(new RegExp(ddd[0], "g"), ddd[day]);

    format = format.replace(new RegExp(MMMM[0], "g"), MMMM[M]);
    format = format.replace(new RegExp(MMM[0], "g"), MMM[M]);

    format = format.replace(/\\(.)/g, "$1");

    return format;
}
