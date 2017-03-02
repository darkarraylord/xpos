/**
 * Created by SMART on 12/23/2015.
 */
(function ($) {
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Class XPosDiscount
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    function XPosDiscount() {
        if (!(this instanceof XPosDiscount)) return new XPosDiscount();
        this.discountItems = {};
        this.discountItemsHasChange = false;
        this.discountOrder = undefined;
    }

    XPosDiscount.prototype.addDiscountItem = function (itemId, value) {
        try {
            this.discountItems[itemId] = XPosDiscountItem(itemId, value);
        } catch (e) {

        }
    };

    XPosDiscount.prototype.setOrderDiscount = function (value) {
        this.getDiscountOrder().setValue(value);
    };

    XPosDiscount.prototype.getDiscountOrder = function (value) {
        if (this.discountOrder === undefined) {
            this.discountOrder = XPosDiscountOrder(0);
        }
        return this.discountOrder;
    };

    XPosDiscount.prototype.changeDiscountItem = function (itemId) {
        var item = this.discountItems[itemId];
        if (item === undefined) {
            item = XPosDiscountItem(itemId);
            this.discountItems[itemId] = item;
        }
        this.discountItemsHasChange = true;
        item.changeDiscount();
        this.recollectTaxAfterDiscount();
        this.discountHasChange();
    };

    XPosDiscount.prototype.isDiscountHasChange = function () {
        return this.discountItemsHasChange || (jQuery('#discount_change').val() == 1);
    };

    XPosDiscount.prototype.discountHasChange = function () {
        var totalDiscount = parseFloat(this.getTotalDiscount());
        var totalDiscountPerItem = parseFloat(this.getTotalDiscountPerItem());
        var discountDisplay = jQuery('#discount_display');
        discountDisplay.val(formatCurrency(totalDiscount, priceFormat));
        discountDisplay.removeAttr('readonly');
        if (totalDiscountPerItem > 0) {
            discountDisplay.attr('readonly', true);
        }
        displayOrder(currentOrder, true);
    };

    XPosDiscount.prototype.getTotalDiscount = function () {
        var total = this.getDiscountOrder().getValue();
        $.each(this.discountItems, function (i, item) {
            total += item.getValue();
        });
        return total;
    };
    XPosDiscount.prototype.getTotalDiscountPerItem = function () {
        var total = 0;
        $.each(this.discountItems, function (i, item) {
            total += item.getValue();
        });
        return total;
    };
    XPosDiscount.prototype.recollectTaxAfterDiscount = function () {
        var total = 0;
        var discount = absValue(jQuery('#discount_display').val());
        var subTotal = absValue(jQuery('#subtotal_value').text());
        var excl = this.getSubTotalExclTax();
        var incl = this.getSubTotalInclTax();
        var taxTotal = absValue(jQuery('#tax_value').text());
        var discountPerItem = this.getTotalDiscountPerItem();
        var AVGTax = this.getAVGTax();
/////////////////////////////////////////////    PRICE INCL TAX    /////////////////////////////////////////////////
        // case apply tax after, discount excl , price incl
        if (
            window.priceIncludesTax == 1 //incl
            && window.apply_tax_after_discount == 1
            && window.apply_discount_on_price == 0 //ex
        ) {
            //case discount per item
            if (discountPerItem > 0) {
                $.each(currentOrder, function (i, item) {
                    var taxRate = item.tax / 100;
                    total += (item.subtotalInclTax - item.discount) * taxRate / (1 + taxRate);
                });
            } else { //case discount
                total = (incl - discount) * AVGTax / (1 + AVGTax);
            }
        }
        // case apply tax after, discount incl , price incl
        if (
            window.priceIncludesTax == 1 //incl
            && window.apply_tax_after_discount == 1
            && window.apply_discount_on_price == 1 //in
        ) {
            //case discount per item
            if (discountPerItem > 0) {
                $.each(currentOrder, function (i, item) {
                    var taxRate = item.tax / 100;
                    total += taxRate * (item.subtotalInclTax - item.discount) / (taxRate + 1) ;
                });
            } else { //case discount
                total = AVGTax * (incl - discount ) / (AVGTax + 1);
            }
        }

        // case apply tax before, discount in || excl , price incl || exc,
        if (
            (window.priceIncludesTax == 1 || window.priceIncludesTax == 0)//incl
            && window.apply_tax_after_discount == 0
        ) {

            //case discount per item
            if (discountPerItem > 0) {
                $.each(currentOrder, function (i, item) {
                    var taxRate = item.tax / 100;
                    total += item.subtotal * taxRate;
                });
            } else { //case discount
                total = excl * AVGTax;
            }
        }

/////////////////////////////////////////////    PRICE ENCL TAX    /////////////////////////////////////////////////
        // case apply tax after, discount excl , price excl
        if (
            window.priceIncludesTax == 0 //ex
            && window.apply_tax_after_discount == 1
            //&& window.apply_discount_on_price == 0 //ex
        ) {
            //case discount per item
            if (discountPerItem > 0) {
                $.each(currentOrder, function (i, item) {
                    var taxRate = item.tax / 100;
                    total += (item.subtotal- item.discount) * taxRate ;
                });
            } else { //case discount
                total = (excl - discount) * AVGTax;
            }
        }
        //// case apply tax after, discount incl , price ex
        //if (
        //    window.priceIncludesTax == 0 //ex
        //    && window.apply_tax_after_discount == 1
        //    && window.apply_discount_on_price == 1 //in
        //) {
        //    //case discount per item
        //    if (discountPerItem > 0) {
        //        $.each(currentOrder, function (i, item) {
        //            var taxRate = item.tax / 100;
        //            total += (item.subtotal - item.discount) * taxRate;
        //        });
        //    } else { //case discount
        //        total = (excl - discount) * AVGTax;
        //    }
        //}


        jQuery('#tax_value').text(formatCurrency(total,priceFormat));

        return Number(total).toFixed(2);
    };
    /*
     * Tax amount calculation
     * @input price, includeTax, taxPercent
     * @param
     * @return float: tax amount
     */
    XPosDiscount.prototype.calTaxAmount = function (subTotal, discount, tax) {

    }

    XPosDiscount.prototype.initDiscountItemFromOrder = function (order) {
        /*Remove discount item array*/
        this.discountItems = {};
        var self = this;
        $.each(order, function (i, orderItem) {
            if (!!orderItem.discount) {
                self.addDiscountItem(orderItem.id, orderItem.discount);
            } else {
                self.addDiscountItem(orderItem.id, 0);
            }
        });
    };

    XPosDiscount.prototype.reRenderAllDiscountItem = function () {
        $.each(this.discountItems, function (i, item) {
            item.render();
        });
    };

    /*
     *  AVG Tax
     * */
    XPosDiscount.prototype.getAVGTax = function () {
        var itemCount = 0;
        var taxRate = 0;
        $.each(currentOrder, function (i, item) {
            itemCount++;
            taxRate += item.tax / 100;
        });
        return taxRate / itemCount;
    };
    /*
     *  get Sub total excl tax
     * */
    XPosDiscount.prototype.getSubTotalExclTax = function () {
        var subTotal = 0;
        $.each(currentOrder, function (i, item) {
            subTotal += item.subtotal;
        });
        return subTotal > 0 ? subTotal : 0;
    };
    /*
     *  get Sub total incl tax
     * */
    XPosDiscount.prototype.getSubTotalInclTax = function () {
        var subTotal = 0;
        $.each(currentOrder, function (i, item) {
            subTotal += item.subtotalInclTax;
        });
        return subTotal > 0 ? subTotal : 0;
    };

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Class XPosDiscountItem
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    function XPosDiscountItem(itemId, value, orderItem) {
        if (!(this instanceof XPosDiscountItem)) return new XPosDiscountItem(itemId, value, orderItem);
        this.id = itemId;
        var _value = arguments.length <= 1 || arguments[1] === undefined ? 0 : parseFloat(arguments[1]).toFixed(2);
        this.setValue(_value);
        this.orderItem = arguments.length <= 2 || arguments[2] === undefined ? false : arguments[2];
    }

    XPosDiscountItem.prototype.changeDiscount = function () {
        var value = this.filterValue(this.getHtmlValue());
        window.isDiscountPerItemMode = true;
        if (value == false) {
            value = 0;
        }
        this.setValue(value);
        this.render();
    };

    XPosDiscountItem.prototype.filterValue = function (value) {
        value = value.trim();
        var regex = /[0-9]*\.?[0-9]+%?$/;

        var convert_value = value.split("%");
        if (isNumber(convert_value[0])) {
            var basePrice = this.getItemSubtotalValue();
            if (convert_value.length == 2) {
                value = absValue(convert_value[0]);
                /*
                 *  if discount > 100% -> return
                 * */
                if( value > 100){
                    value = 0;
                }
                value = value * basePrice / 100;
                value = value.toFixed(2);
            } else {
                /*
                *  if discount > 100% -> return
                * */
                if( value > basePrice){
                    value = 0;
                }
                value = '' + value;
            }
            return value;
        }
        return false;
    };

    XPosDiscountItem.prototype.render = function () {
        this.getHtmlElement().val(this.value);
    };

    XPosDiscountItem.prototype.getHtmlValue = function () {
        return this.getHtmlElement().val();
    };

    XPosDiscountItem.prototype.getItemSubtotalValue = function () {
        var item = this.id;
        var taxItem = jQuery('.tax-item-id-' + item).attr('tax-item-id');
        var value = absValue(this.getHtmlElement().parent('td').siblings('td.subtotal').children('.subtotal-list').text());
        //display excl, discount in incl
        if ((window.cartSubtotalDisplay == 1 || window.cartSubtotalDisplay == 3) && window.apply_discount_on_price == 1) {
            value = value + value * taxItem / 100;
            ;
        }
        //display incl, discount in excl
        if (window.cartSubtotalDisplay == 2 && window.apply_discount_on_price == 0) {
            value = value / (1 + taxItem / 100);
        }
        return !!this.getHtmlElement().length ? value : 0;
    };

    XPosDiscountItem.prototype.getHtmlElement = function () {
        return $('#items_area #item-discount-' + this.id);
    };

    XPosDiscountItem.prototype.setValue = function (value) {
        value = parseFloat(value);
        this.value = value;
        this.seOrderItemtValue(value)
    };

    XPosDiscountItem.prototype.getValue = function () {
        return this.value;
    };

    XPosDiscountItem.prototype.seOrderItemtValue = function (value) {
        if (this.getOrderItem() !== false) {
            this.getOrderItem().discount = value;
        }
    };

    XPosDiscountItem.prototype.getOrderItem = function (value) {
        if (!!window.currentOrder[this.id]) return window.currentOrder[this.id];
        return false;
    };

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Class XPosDiscountOrder
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    function XPosDiscountOrder(value) {
        if (!(this instanceof XPosDiscountOrder)) return new XPosDiscountOrder(value);
        var _value = arguments.length <= 0 || arguments[0] === undefined ? false : arguments[0];
        this.setValue(value);
    }

    XPosDiscountOrder.prototype.setValue = function (value) {
        value = parseFloat(value);
        this.value = value;
    };

    XPosDiscountOrder.prototype.getValue = function () {
        return this.value;
    };

    window.xposDiscount = XPosDiscount();
})(jQuery);
function getCurrentItemQty (itemId) {
    var string = jQuery('#item-qty-' + itemId).val();
    var value = string.replace(',', '.');
    var class_name = jQuery('#item-qty-' + itemId).attr('class');
    if (class_name.indexOf('qty_decimal') == -1)
        var newQty = parseInt(value);
    else
        var newQty = parseFloat(value);

    return newQty? newQty : 1;
};
