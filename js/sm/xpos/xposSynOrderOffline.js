/**
 * Created by vjcspy on 7/28/15.
 */
var syncOfflineData = function () {
    "use strict";

};

syncOfflineData.prototype = {
    synchingData: false,
    netWorkOnline: false,
    timeRecheckNetWork: 30000,
    currentStoreName: '',
    orderComplete: [],
    holdWhenCheckOutOnline: true,
    initialize: function (data) {
        if (!data) {
            data = {};
        } else {
            this.timeRecheckNetWork = data.timeRecheckNetWork;
            this.holdWhenCheckOutOnline = data.holdWhenCheckOutOnline;
        }
    },
    Ping: function Ping(url, timeout) {
        timeout = timeout || 5000;
        url = url || window.BASE_URL;
        var timer = null;

        return jQuery.Deferred(function deferred(defer) {

            var img = new Image();
            img.onload = function () {
                success("onload");
            };
            img.onerror = function () {
                success("onerror");
            };

            var start = new Date();
            img.src = url += ("?cache=" + +start);
            timer = window.setTimeout(function timer() {
                fail();
            }, timeout);

            function cleanup() {
                window.clearTimeout(timer);
                timer = img = null;
            }

            function success(on) {
                cleanup();
                this.netWorkOnline = true;
                defer.resolve(true, url, new Date() - start, on);
            }

            function fail() {
                cleanup();
                this.netWorkOnline = false;
                defer.reject(false, url, new Date() - start, "timeout");
            }

        }).promise();
    },
    setStatus: function (isSynching) {
        "use strict";
        if (isSynching) {
            this.synchingData = true;
            jQuery('#count_pending_orders').html('Synching');
            $jQuery('#link_sync_order').removeAttr('onclick');
        } else {
            this.synchingData = false;
            var orders = $.jStorage.get("orders");
            jQuery('#count_pending_orders').html(orders.length);
            //jQuery('#link_sync_order').attr('onclick', 'window.XPosSyncData.syncData()');
        }
    },
    syncData: function () {
        "use strict";
        if (isOnline() && this.holdWhenCheckOutOnline == true) {
            iLog('Send Offline Order. Hold because you are checking online!');
            setTimeout(function () {
                window.XPosSyncData.syncData();
            }, window.XPosSyncData.timeRecheckNetWork);
            return;
        }
        if ($.jStorage.get("orders") == null) {
            iLog('Send Offline Order. Nothing to send!');
            setTimeout(function () {
                window.XPosSyncData.syncData();
            }, window.XPosSyncData.timeRecheckNetWork);
            return;
        }
        this.Ping(window.BASE_URL, 5000).done(function (success, url, time, on) {
            var orders = $.jStorage.get("orders");
            numOfOrder = orders.length;
            if (numOfOrder > 0 && orders[0].length) {
                window.XPosSyncData.setStatus(true);
                jQuery.ajax({
                    url: window.url_offline_order,
                    data: orders[0],
                    dataType: 'json',
                    type: 'POST',
                    success: function (data) {
                        if (data['status'] == 'success') {
                            iLog('Send Offline Order. Success!');
                            window.XPosSyncData.saveOrderComplete(orders[0]);
                        }
                        window.XPosSyncData.loadAjax(orders, 1);
                    }
                });
            }
            else {
                window.XPosSyncData.setStatus(false);
                iLog('Send Offline Order. Nothing to send!');
                setTimeout(function () {
                    window.XPosSyncData.syncData();
                }, window.XPosSyncData.timeRecheckNetWork);
            }
            transactionMoneyLoaded = false;
        }).fail(function (failure, url, time, on) {
            console.log("ping fail", arguments);
            iLog('Send Offline Order. Not online!');
            setTimeout(function () {
                window.XPosSyncData.syncData();
            }, window.XPosSyncData.timeRecheckNetWork);
        });
    },
    loadAjax: function (orders, vt) {
        if (isOnline() && this.holdWhenCheckOutOnline == true) {
            iLog('Send Offline Order. Hold because checking online!');
            return;
        }
        "use strict";
        iLog('Send next order: ' + vt);
        if (numOfOrder > vt) {
            jQuery.ajax({
                url: window.url_offline_order,
                data: orders[vt],
                dataType: 'json',
                type: 'POST',
                success: function (data) {
                    if (data['status'] == 'success') {
                        console.log('data success!');
                        window.XPosSyncData.saveOrderComplete(orders[vt]);
                    }
                    window.XPosSyncData.loadAjax(orders, vt + 1);
                }
            });
        } else {
            window.XPosSyncData.removeOrderComplete();
        }
    },
    saveOrderComplete: function (order) {
        this.orderComplete.push(order);
    },

    removeOrderComplete: function () {
        var orders = $.jStorage.get("orders");
        var numOfSuccsessOrder = this.orderComplete.length;
        for (i = 0; i < this.orderComplete.length; i++) {
            var vt = orders.indexOf(this.orderComplete[i]);
            if (vt != -1) {
                orders.splice(vt, 1);
            }
        }
        $.jStorage.set("orders", orders);
        jQuery('#count_pending_orders').html(orders.length);
        this.removeAllElementOrderComplete();
        iLog('Send ' + numOfSuccsessOrder + ' Order Complete!');
        setTimeout(function () {
            window.XPosSyncData.syncData();
        }, window.XPosSyncData.timeRecheckNetWork);
    },
    removeAllElementOrderComplete: function () {
        this.orderComplete.splice(0, this.orderComplete.length);
        console.log('current Order Complete: ' + this.orderComplete);
    },

    sendDataOld: function () {
        "use strict";
        submitOfflineNewT();
    },

    hideLoadingMask: function () {
        Element.hide('loading-mask');
    },
    displayLoadingMask: function () {
        var loaderArea = $$('#html-body .wrapper')[0]; // Blocks all page
        Position.clone($(loaderArea), $('loading-mask'), {offsetLeft: -2});
        toggleSelectsUnderBlock($('loading-mask'), false);
        Element.show('loading-mask');
    }

}
;


function ping() {
    window.XPosSyncData.Ping(window.BASE_URL, 5000).done(function (success, url, time, on) {
        console.log("ping done", arguments);
        ping();
    }).fail(function (failure, url, time, on) {
        console.log("ping fail", arguments);
        ping();
    });
}

jQuery(document).ready(function () {
    "use strict";
    //window.setTimeout(ping, 1000);
    if (false) {
        var data = {};
        data.timeRecheckNetWork = 30000;
        data.holdWhenCheckOutOnline = true;
        window.XPosSyncData = new syncOfflineData(data);
        window.XPosSyncData.syncData();
        if (true) {
            $jQuery('#link_sync_order').removeAttr('onclick');
        }
    }
});

function prepareDataForOfflineOrderTable() {
    var orders = window.tempData;
    var data = [];
    if (orders == null) {
        return []
    }
    ;
    orders = toObject(orders);
    jQuery.each(orders, function (kos, order) {
        data.push([
            kos,
            parseInt(kos) + 1,
            order['customer_name'],
            order['time'],
            parseFloat(order['grand_total']).toFixed(2),
            kos,
        ]);
    });
    return data;
}
function updateDataTableSelectAllCtrl(table) {
    var $table = table.table().node();
    var $chkbox_all = jQuery('tbody input[type="checkbox"]', $table);
    var $chkbox_checked = jQuery('tbody input[type="checkbox"]:checked', $table);
    var chkbox_select_all = jQuery('thead input[name="select_all"]', $table).get(0);

    // If none of the checkboxes are checked
    if ($chkbox_checked.length === 0) {
        chkbox_select_all.checked = false;
        if ('indeterminate' in chkbox_select_all) {
            chkbox_select_all.indeterminate = false;
        }

        // If all of the checkboxes are checked
    } else if ($chkbox_checked.length === $chkbox_all.length) {
        chkbox_select_all.checked = true;
        if ('indeterminate' in chkbox_select_all) {
            chkbox_select_all.indeterminate = false;
        }

        // If some of the checkboxes are checked
    } else {
        chkbox_select_all.checked = true;
        if ('indeterminate' in chkbox_select_all) {
            chkbox_select_all.indeterminate = true;
        }
    }
}


function reDrawOfflineOrderTable(table) {
    table.clear()
    window.tempData = $.jStorage.get("orders");
    table.rows.add(prepareDataForOfflineOrderTable()); // Add new data
    table.columns.adjust().draw(); // Redraw the DataTable
}
function reSaveOfflineOrder() {
    var orders = getTempDataOfflineOrder();
    var orderNeedRemove = getOrderNeedRemove();
    if (orderNeedRemove.length == 0) {
        return;
    }
    jQuery(orderNeedRemove).each(function (key, value) {
        delete orders[value];
    });
    flushOrderNeedRemove();
    saveTempDataOfflineOrder(orders);
    $.jStorage.set("orders", window.tempData);
    jQuery('#count_pending_orders').html(window.tempData.length);
}
function getSelectedOfflineOrder() {
    selectedRows = [];
    jQuery(".offline_order_grid_table input.checkbox-off:checked").not(":hidden").each(function (index, val) {
        selectedRows.push(jQuery(this).attr('pos'));
    });
    return selectedRows;
}
var loadingIcon = "<div class='uil-facebook-css loading-icon' style='-webkit-transform:scale(0.6)'><div></div><div></div><div></div></div>";
/*
 * change loading effect
 * */
function changeStatusLoadingOfflineOrderRow(row, parent) {
    parent.append(loadingIcon);
    jQuery('.complete-action').attr('disabled', true);
    jQuery('.select-off').attr("disabled", true);
    row.hide();
}
function updateStatusOfflineOrderRow(flag, row, parent, id, icId) {
    if (flag) {
        var linkToViewOrder = '<a href="javascript:void(0)" style="margin:8px;10px" class="icid" icid="' + icId + '" oid="' + id + '">' + icId + '</a>';
        row.closest('tr').removeClass('selected').addClass('success');
        row.parent().siblings('td').first().replaceWith(linkToViewOrder);
        parent.find('.loading-icon').replaceWith("<div class='checkmark'></div>");
        row.parent().siblings('td').last().find('.select-off').attr("disabled", true);
    } else {
        row.closest('tr').removeClass('selected').addClass('error');
        parent.find('.loading-icon').remove();
        row.show();
    }
    jQuery('.complete-action').removeAttr('disabled');
    jQuery('.select-off').removeAttr("disabled");
}
function completeSingleOfflineOrder(index, table, action) {
    if (!isOnline()) {
        alert('You are not online!');
        return false;
    }
    var url = jQuery('.complete-offline-link').val();
    var orders = getTempDataOfflineOrder();
    var dataSend = '';
    jQuery.each(orders[index], function (k, v) {
        if (typeof v == 'string') {
            v = v.replace(/ /g, '');
        }
        if(v == 'till_id'){
            var till_id =  order.tillId?order.tillId:'';
            dataSend += k + '=' + till_id + '&';
        }else{
            dataSend += k + '=' + v + '&';
        }
    });
    dataSend += 'action=' + action;
    var selectedCk = jQuery('#checkbox-off-' + index);
    var parent = selectedCk.parent();
    /*increase free count*/
    if (window.isFreeVerion) {
        window.currentOrderAmount = window.currentOrderAmount + 1;
        jQuery('#limit-order-label').html(window.currentOrderAmount + '/ ' + window.maxOrderAmount + 'orders.');
    }
    jQuery.ajax({
        url: url,
        data: dataSend,
        dataType: 'json',
        type: 'GET',
        beforeSend: function () {
            changeStatusLoadingOfflineOrderRow(selectedCk, parent);
        },
        success: function (res) {
            var flag = false;
            if (res['status'] == 'success') {
                setOrderNeedRemove(index);
                //delete orders[index];
                //saveTempDataOfflineOrder(orders);
                flag = true;
            } else {
                if (window.isFreeVerion) {
                    window.currentOrderAmount = window.currentOrderAmount - 1;
                    jQuery('#limit-order-label').html(window.currentOrderAmount + '/ ' + window.maxOrderAmount + 'orders.');
                }
            }
            updateStatusOfflineOrderRow(flag, selectedCk, parent, res.id, res.msg);
        }
    });
}
function cancelOfflineOrders(rows, table) {
    //var orders = getTempDataOfflineOrder();
    jQuery.each(rows, function (k, v) {
        //delete  orders[v];
        setOrderNeedRemove(v[0]);
    });
    table.rows('.selected').remove().draw(false);
    //saveTempDataOfflineOrder(orders);
}
function saveTempDataOfflineOrder(orders) {
    orders = jQuery.map(orders, function (value, index) {
        return [value];
    });
    window.tempData = orders;
}
function getTempDataOfflineOrder() {
    return toObject(window.tempData);
}
function toObject(arr) {
    var rv = {};
    for (var i = 0; i < arr.length; ++i)
        rv[i] = arr[i];
    return rv;
}

function onViewOfflineOrder(id) {
    var symbol = order.currencySymbol;
    var tr = "<tr><td class=\"align-left\">{name}</td><td>"
        + symbol + "{price}</td><td>"
        + "{qty}</td><td>"
        + symbol + "0</td><td>"
        + symbol + "{subtotal}</td></tr>"
    jQuery('.off-order-id').text(id);
    var ordersTemp = getTempDataOfflineOrder();
    var orderTemp = ordersTemp[id - 1];
    var payment = orderTemp["payment[method]"];
    var cashIn = parseFloat(orderTemp["cash-in"]).toFixed(2);
    var totalDiscount = 0;
    var subTotal = parseFloat(orderTemp['sub_total']).toFixed(2);
    var grandTotal = parseFloat(orderTemp['grand_total']).toFixed(2);
    var taxTotal = parseFloat(orderTemp['tax_total']).toFixed(2);
    var paymentLabel = 'X-POS / CASH PAYMENT';
    if (payment != 'xpayment_cashpayment') {
        paymentLabel = 'X-POS Credit Card';
    }
    var shippingLabel = 'Pickup Shipping - Shipping $0.00';
    var arrItem = [];
    var arrQty = [];
    var arrName = [];
    var arrCustomPrice = {};
    var arrPrice = {};
    var pattItem = /item\[[0-9]+\-[0-9]+\]/g;
    var pattQty = /item\[[0-9]+\-[0-9]+\]\[qty\]/g;
    var pattName = /item\[[0-9]+\-[0-9]+\]\[name\]/g;
    var pattCustomPrice = /item\[[0-9]+\-[0-9]+\]\[custom_price\]/g;
    var pattPrice = /item\[[0-9]+\-[0-9]+\]\[price\]/g;
    var pattDiscount = /item\[[0-9]+\-[0-9]+\]\[discount\]/g;
    var countItem = -1;
    var itemIdTemp = 0;
    jQuery.each(orderTemp, function (k, v) {
        //dont support discount
        var aItemPatt = k.match(pattItem);
        if (aItemPatt && aItemPatt.length > 0) {
            var aItemPattResult = aItemPatt.toString().match(/[0-9]+/g).toString();
            if (aItemPattResult && aItemPattResult.length > 0) {
                var natureTemp = aItemPattResult;
                var tmp = aItemPattResult.split(',');
                var itemId = tmp[0];
                if (itemIdTemp != natureTemp) {
                    itemIdTemp = natureTemp;
                    arrItem.push(itemId);
                    countItem++;
                }
            }
        }
        if (k.match(pattName)) {
            arrName.push(v)
        }
        if (k.match(pattPrice)) {
            var tmp = aItemPattResult.split(',');
            var obj = {};
            obj[tmp[0] + '-' + countItem] = v;
            jQuery.extend(arrPrice, obj);
        }
        if (k.match(pattCustomPrice)) {
            var obj = {};
            obj[tmp[0] + '-' + countItem] = v;
            jQuery.extend(arrCustomPrice, obj);
        }
        if (k.match(pattQty)) {
            arrQty.push(v);
        }
    });
    var tBody = jQuery(".off-order-items-tbody");
    tBody.empty();
    var count = 0;
    jQuery.each(arrItem, function (key, value) {
        var item = {};
        item.qty = arrQty[key];
        item.name = productData[value].name;
        if (productData[value].sku == 'custom_sales_product_for_xpos') {
            item.name = arrName[count];
            count++;
        }
        item.price = arrPrice[value + '-' + key];
        if (arrCustomPrice[value + '-' + key]) {
            item.price = parseFloat(arrCustomPrice[value + '-' + key]).toFixed(2);
        }
        //item.discount = arrDiscount[key];
        item.discount = 0;
        //totalDiscount += parseFloat(item.discount);
        item.subtotal = parseFloat(item.qty * item.price).toFixed(2);
        var row = nano(tr, item);
        tBody.append(row);
    });
    jQuery('.popup-list-offline-order').bPopup().close();
    jQuery('.off-order-payment').text(paymentLabel);
    jQuery('.off-order-shipment').text(shippingLabel);
    jQuery('.off-total-paid').text(symbol + '' + cashIn);
    jQuery('.off-total-discount').text(symbol + '' + "0.00");
    jQuery('.off-sub-total').text(symbol + '' + subTotal);
    jQuery('.off-total-tax').text(symbol + '' + taxTotal);
    jQuery('.off-grand-total').text(symbol + '' + grandTotal);
    showOfflineDetailOrder();
}
function showOfflineOrderList() {
    window.needReSaveOfflineOrder = true;
    jQuery('.popup-list-offline-order').bPopup({
        modalClose: true,
        opacity: 0.6,
        speed: 450,
        transition: 'slideIn',
        positionStyle: 'fixed', //'fixed' or 'absolute'
        onClose: function () {
            if(window.needReSaveOfflineOrder){
                reSaveOfflineOrder();
            }
        }
    });
}
function showOfflineDetailOrder() {
    jQuery('.popup-view-offline-order').bPopup({
        modalClose: false,
        opacity: 0.6,
        speed: 450,
        transition: 'slideIn',
        positionStyle: 'fixed', //'fixed' or 'absolute'
        onClose: function () {
            showOfflineOrderList();
        }
    });
}
/*
 * validate free
 *
 * */
function checkFree() {
    if (
        (parseNumber(window.currentOrderAmount) >= window.maxOrderAmount) &&
        window.isFreeVerion
    ) {
        callFreeWarningMessage();
        return false;
    }
    return true;
}
function callFreeWarningMessage() {
    swal({
        title: "<small style='color:#F8BB86'>Warning!</small>",
        text: '<span style="color:#ff6713">' + "You've exceeded your order limit for this month, to continue to use the X-POS, please upgrade your plan at <a href='http://xpos.smartosc.com/x-pos/#plan-pricing' target='_blank'>Here</a>" + '<span>',
        html: true
    });
}
function callFreeWarningMessageCount(num) {
    swal({
        title: "<small style='color:#F8BB86'>Oops!</small>",
        text: '<span style="color:#ff6713">There are ' + num + ' order(s) left for this month.<span>',
        html: true
    });
}
function setOrderNeedRemove(index) {
    if (typeof window.orderNeedRemove != 'object') {
        window.orderNeedRemove = [];
        window.orderNeedRemove.push(index);
    } else {
        window.orderNeedRemove.push(index);
    }
}
function getOrderNeedRemove() {
    if (typeof window.orderNeedRemove != 'object') {
        return window.orderNeedRemove = []
    }
    window.orderNeedRemove = eliminateDuplicates(window.orderNeedRemove);
    return window.orderNeedRemove;
}
function flushOrderNeedRemove() {
    window.orderNeedRemove = [];
}
function eliminateDuplicates(arr) {
    var i,
        len = arr.length,
        out = [],
        obj = {};

    for (i = 0; i < len; i++) {
        obj[arr[i]] = 0;
    }
    for (i in obj) {
        out.push(i);
    }
    return out;
}
