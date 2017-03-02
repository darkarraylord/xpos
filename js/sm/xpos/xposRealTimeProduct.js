/**
 * Created by vjcspy on 7/17/15.
 */
var xposLoadProduct = function () {
};
xposLoadProduct.prototype = {
    cache_key: 'xpos_data_cache_key',
    productUpdated: 0,
    hasNewDataAllProduct: 0,
    checkNewInterval: 6000,
    dataLoadInterval: 0,
    fistCheckData: true,
    initialize: function (data) {
        "use strict";

    },
    checkCacheData: function (whenGetDataFromServer, whenExistCache) {
        var reCheckCacheData= whenExistCache;
        var firstCheck = firstCheckXPosProductData;
        firstCheckXPosProductData = false;
        var cache_key = 'xpos_data_cache_key';

        var append = '';

        var xpos_data_cache_key = $.jStorage.get(this.cache_key, '0');
        if (xpos_data_cache_key != '0') {
            //if cache existed
            if(this.fistCheckData == true){
                jQuery('#search-box').focus();
                this.fistCheckData = false;
            }
            append = (xPosCheckProductUrl.indexOf('?') > -1) ? '&cache_key=' + xpos_data_cache_key : '?cKeR=' + xpos_data_cache_key;
            append += '&storeId=' + window.multiStoreView.getCurrentStoreId();
            jQuery.getJSON(xPosCheckRealTimeUrl + append, function (json) {
                if (json.hasOwnProperty('need_update_allProduct') && !!json.need_update_allProduct) {
                    iLog('Has cache front end but server cache not exist or diff, will reload all data');
                    this.hasNewDataAllProduct = 1;
                    this.productUpdated = 0;
                    $.jStorage.set('product_page_storage', 1);
                    $.jStorage.deleteKey(cache_key);
                    whenGetDataFromServer();
                } else {
                    if (json.hasOwnProperty('update_real_time') && json.update_real_time == true) {
                        iLog('Has product update real time, just load new product');
                        var dataNewProduct = json.dataProduct;
                        if (dataNewProduct != null) {
                            jQuery.each(dataNewProduct, function (i, item) {
                                var dataProductN = item;
                                productData[item.id] = dataProductN;
                            });
                            $.jStorage.set('productData', productData);
                        }
                        $.jStorage.set('xpos_data_cache_key', json.server_cacheKey);
                        window.show_cate_default = 1;
                        if (window.show_cate_default == 1) {
                            showCategoryDefault(true);
                            window.show_cate_default = 0;
                        }
                        this.setStatusUpdatingRealTime();
                        if (firstCheck) {
                            varienGlobalEvents.fireEvent('xPos_loaded_product_data');
                        }
                    } else {
                        //iLog('Update realtime', json.update_real_time);
                        if (window.show_cate_default == null)
                            window.show_cate_default = 1;
                        if (window.show_cate_default == 1) {
                            showCategoryDefault(true);
                            window.show_cate_default = 0;
                        }
                        if (firstCheck) {
                            varienGlobalEvents.fireEvent('xPos_loaded_product_data');
                        }
                    }
                    reCheckCacheData();
                }
            }.bind(window.XposLoadProduct));
        } else {
            //if cache not existed
            iLog('cache key not exist');
            whenGetDataFromServer();
        }
    },

    getDataProductFromServer: function (page) {
        iLog('getDataProductFromSv');
        setStatusXposWorking(' | >>Loading Product ');
        var warehouseId = $.jStorage.get('xpos_warehouse');
        var append = (loadProductUrl.indexOf('?') > -1) ? '&page=' + page : '?page=' + page;
        if (warehouseId != null) {
            append += '&warehouse=' + warehouseId.warehouse_id;
        }
        append += '&storeId=' + window.multiStoreView.getCurrentStoreId();
        jQuery.getJSON(loadProductUrl + append, function (json) {

            data = json;
            var totalLoad = data['totalLoad'];
            var totalProduct = data['totalProduct'];
            var totalSaved;
            this.productUpdated += totalLoad;
            iLog('ProductUpdated', this.productUpdated);
            this.saveData(data, page);
            var next = page + 1;
            $.jStorage.set('product_page_storage', next);
            var _plural = totalProduct > 1 ? ' products' : ' product';
            totalSaved = Object.keys(productData).length;
            var _updatedPercentage = (totalSaved / totalProduct * 100).toFixed(0);
            var _status = '';
            if (this.hasNewDataAllProduct == 1) {
                _updatedPercentage = (this.productUpdated / totalProduct * 100).toFixed(0);
                _status = ' | Updating ' + _updatedPercentage + '% of ' + totalProduct + _plural + ' (saved ' + totalSaved + ')';
            } else {
                _status = ' | ' + _updatedPercentage + '% of ' + totalProduct + _plural + ' (saved ' + totalSaved + ')';
            }
            this.setStatus(_status);
            if (_updatedPercentage > 99) {
                showCategoryDefault(true);
            }

            if (totalLoad == 0) {
                _status = ' | ' + _updatedPercentage + '% of ' + totalProduct + _plural + ' (saved ' + totalSaved + ')';
                this.setStatus(_status);
                setStatusXposWorking('Almost finished loading products ');
                iLog('total Load == 0');
                if (data.hasOwnProperty('xpos_cache_key')) {
                    iLog('set cache key');
                    $.jStorage.set('xpos_data_cache_key', data['xpos_cache_key']);
                    this.hasNewDataAllProduct = 0;
                    this.productUpdated = 0;
                    this.fistCheckData = true;
                }
                jQuery('#search-box').focus();
                varienGlobalEvents.fireEvent('xPos_loaded_product_data');
                setTimeout(function () {
                    setStatusXposWorking('');
                    if(jQuery('#right').is(":visible")!= false) {
                        loadProductData();
                    }
                }.bind(window.XposLoadProduct), this.dataLoadInterval);
            } else {
                if (!isOverStorage()) {
                    iLog('next page to Load', next);
                    setTimeout(function () {
                        this.getDataProductFromServer(next);
                    }.bind(window.XposLoadProduct), this.dataLoadInterval);
                }
            }
        }.bind(window.XposLoadProduct));
    },
    saveData: function (data, page, callback) {
        //productData = $.jStorage.get('productData', 0);
        if (productData == 0)
            productData = {};

        var productInfo = data['productInfo'];
        jQuery.each(productInfo, function (i, item) {
            productData[item.id] = item;
        });
        $.jStorage.set('productData', productData);

        if (typeof callback === "function") {
            callback();
        }
    },
    setStatus: function (status) {
        "use strict";
        jQuery('#status').text(status);
    },
    setStatusUpdatingRealTime: function () {
        "use strict";
        setStatusXposWorking('Detecting a change');
        setTimeout(function () {
            setStatusXposWorking('Synced product');
        }, 1000);
        setTimeout(function () {
            setStatusXposWorking('');
        }, 3000);

    }
};
var XPOS_LOAD_PRODUCT = new xposLoadProduct();
window.XposLoadProduct = XPOS_LOAD_PRODUCT;
function loadProductData() {
    "use strict";
    if (offlineSearch == false) {
        iLog('Setting Search Online Only.');
        varienGlobalEvents.fireEvent('xPos_loaded_product_data');
        //Only access online data in X-POS
        return true;
    }
    window.XposLoadProduct.checkCacheData(function () {
        if(!isOverStorage()){
            var currentPage = ProductPage.getCurrentPage();
            iLog('true call back with currentPage = ', currentPage);
            window.XposLoadProduct.getDataProductFromServer(currentPage);
        }
    }, function () {
        if(!isOverStorage()){
            setTimeout(loadProductData, window.XposLoadProduct.checkNewInterval);
        }
    });
}
function isOverStorage(){
    var linkConfig = jQuery('#admin_menu_item').children('a')[0].href;
    /*
    * see \js\sm\xpos\jstorage\jstorage.js line 455
    * */
    if (window.overStorage) {
        swal({
            title: "<small>Local storage quota exceeded!!</small>!",
            text: '<span style="color:#B71C1C">' + 'Please disable cache of product at <a href="' + linkConfig + '" target="_blank" class="admin" style="color:#B71C1C;font-weight:bold;">here </a>' + 'or increase limit quota of browser.' + "<span>",
            html: true
        });
        return true;
    }
    return false;
}
