/**
 * Created by vjcspy on 9/14/2015.
 */
window.currentGiftRegistryProduct = [];
var giftRegistryWt = function () {
};
giftRegistryWt.prototype = {
    numOfResult: 50,
    initialize: function (data) {
        "use strict";
    },
    setFieldSearchRegistry: function () {
        jQuery('#registry_search_field').keyup(function (event) {
            var keyCode = event.keyCode || event.which;
            //delay(function () {
            if (event.which == 13) {
                var registryId = jQuery('#registry_search_field').val();
                if (registryId == '') {
                    //var default_cate = jQuery("#cate_default").val();
                    var default_cate = $.jStorage.get("cate_default");
                    var cate_default_name = $.jStorage.get("cate_default_name");
                    if (default_cate >= 0) {
                        displayProduct(default_cate, 0);
                        //initScroll("#product-info");
                        // alert(cate_default_name);
                        jQuery('#category-selected').text(cate_default_name);
                        jQuery('#category-selected').append('<i>icon</i>');
                        jQuery('#category-list').slideUp('slow');
                        jQuery('#category-selected').removeClass('show').addClass('hide');

                    }
                    else {
                        jQuery('#category-selected').text("Select category");
                        jQuery('#category-selected').append('<i>icon</i>');
                    }
                }
                else {
                    jQuery("#product-info").empty();
                }


                /*TODO: Search product by giftregistry ID*/

                registryId = registryId.toLowerCase();
                this.getDataItemsByGiftRegistryId(registryId);

            }
        }.bind(this));
    },

    getDataItemsByGiftRegistryId: function (giftRegistryId) {
        var append = (window.urlGetItemsGiftRegistry.indexOf('?') > -1) ? '&giftRegistryId=' + giftRegistryId : '?giftRegistryId=' + giftRegistryId;
        showMask();
        jQuery.getJSON(window.urlGetItemsGiftRegistry + append, function (json) {
            hideLoadingMask();
            if (json.hasOwnProperty('noData') && json.noData) {
                /*Notice here*/
                return null;
            } else {
                var productInfo = json;
                var lstProduct = [];
                if (productInfo != null) {
                    jQuery.each(productInfo, function (i, item) {
                        var currentItem = [];
                        currentItem.id = item.productId;
                        currentItem.priority = item.priority;
                        currentItem.desired = item.desired;
                        currentItem.received = item.received;
                        currentItem.purchased = item.purchased;
                        lstProduct.push(currentItem);
                    });
                }

                /*jQuery.each(productData, function (i, item) {
                 if (item['name'] == null || item['sku'] == null) {
                 return true;
                 }
                 if (result.length < number_result &&
                 (item['searchString'] != null && item['searchString'].toLowerCase().match(term))) {
                 result.push(item);
                 }
                 });*/


                var data = lstProduct;
                window.currentGiftRegistryProduct = lstProduct;
                if (data.length == 1)
                    $.jStorage.set('searchData', data);
                else $.jStorage.set('searchData', null);
                if (data.length == 0) {
                    jQuery('#category-selected').text("No search registry result for : " + giftRegistryId);
                    jQuery('#category-selected').append('<i>icon</i>');
                }

                var count_rs = 0;
                jQuery.each(data, function (i, item) {
                    window.isSearchRegistry = true;
                    displayProductItem(item, true);
                    count_rs++;
                });
                //window.isSearchRegistry = false;
                var result_string = "result";
                if (count_rs > 1) {
                    result_string = "results";
                }
                jQuery('#category-selected').text(count_rs + ' ' + result_string + ' for: ' + giftRegistryId);
                jQuery('#category-selected').append('<i>icon</i>');
                //initScroll("#product-info");
                show_customer_search();
            }

        });
    }
};
giftRegistryWt = new giftRegistryWt();
jQuery(document).ready(function () {
    giftRegistryWt.setFieldSearchRegistry();
});
