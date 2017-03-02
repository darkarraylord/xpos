<?php

/**
 * Created by PhpStorm.
 * User: vjcspy
 * Date: 7/7/15
 * Time: 11:50 AM
 */
class SM_XPos_Helper_ConfigXPOS extends Mage_Core_Helper_Abstract {
    private $_offline = null;

    /*GROUP GENERAL OPTIONS*/
    public function getGeneral($field, $storeId = null) {
        $config = Mage::getStoreConfig('xpos/general/' . $field, $storeId);

        return $config;
    }

    /*GROUP GUEST*/
    public function getGuest($field, $storeId = null) {
        $config = Mage::getStoreConfig('xpos/guest/' . $field, $storeId);

        return $config;
    }


    /*GROUP OFFLINE MODE*/
    public function getOffline() {
        if (!$this->_offline) {
            $config = array();

            /**/
            $dataReloadCached = Mage::getStoreConfig('xpos/offline/data_reload_interval');
            $config['data_reload_interval'] = $dataReloadCached;
            /**/
            $dataLoadProductInterval = Mage::getStoreConfig('xpos/offline/data_load_interval');
            $config['data_load_interval'] = $dataLoadProductInterval;
            /**/
            $this->_offline = $config;
        }

        return $this->_offline;
    }

    public function getCrontab($field) {
        $config = Mage::getStoreConfig('xpos/cronJob/' . $field);

        return $config;
    }


    /*CONFIG XPOS NEW*/
    private $currentStoreId = null;

    /**
     * PHP 5 allows developers to declare constructor methods for classes.
     * Classes which have a constructor method call this method on each newly-created object,
     * so it is suitable for any initialization that the object may need before it is used.
     *
     * Note: Parent constructors are not called implicitly if the child class defines a constructor.
     * In order to run a parent constructor, a call to parent::__construct() within the child constructor is required.
     *
     * param [ mixed $args [, $... ]]
     * @return void
     * @link http://php.net/manual/en/language.oop5.decon.php
     */
    function __construct() {
        // TODO: Implement __construct() method.
        if (Mage::getSingleton('adminhtml/session')->getCurrentStoreView() != null) {
            $this->currentStoreId = Mage::getSingleton('adminhtml/session')->getCurrentStoreView();
        } else {
            $this->currentStoreId = Mage::getStoreConfig('xpos/general/storeid');
        }
    }

    public function getEnable($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/general/enabled', $storeId);
    }

    public function getEnableShortcut($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return 1;
    }

    public function getCreateInvoiceDisplay($storeId = null) {
        /*TODO: REMOVE SETTING WILL ALWAY RETURN 1*/

        return 1;

        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/general/create_invoice_display', $storeId);
    }

    public function getCreateShipmentDisplay($storeId = null) {
        /*TODO: REMOVE SETTING WILL ALWAY RETURN 1*/

        return 1;

        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/general/create_shipment_display', $storeId);
    }

    public function getCreateInvoice($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/checkout/create_invoice', $storeId);
    }

    public function getCreateIShipmment($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/checkout/create_shipment', $storeId);
    }

    public function getDefaultPayment($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/checkout/default_payment', $storeId);
    }

    public function getDefaultShipping($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/checkout/default_shipping', $storeId);
    }

    public function getOnlyUseDefaultShipping($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/checkout/only_use_default_shipping', $storeId);
    }

    public function getOnlfyDiscount($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/checkout/onfly_discount', $storeId);
    }

    public function getCheckOutConfirm($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/checkout/checkoutcomfirm', $storeId);
    }

    public function getPrintCashTransfer($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/checkout/printcashtransfer', $storeId);
    }

    public function getIntegrateXmwhEnable($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/general/integrate_xmwh_enabled', $storeId);
    }

    public function getEnableTill($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/general/enable_till', $storeId);
    }

    public function getEnableCashier($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/general/enabled_cashier', $storeId);
    }

    public function getEnableAutoLogOut($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/general/enabled_auto_logout', $storeId);
    }


    public function getLoginTimeOut($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/general/login_time_out', $storeId);
    }

    public function getOfflineSearch($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/xpos_mode/offlinesearch', $storeId);
    }

    public function getOfflineSearchCustomer($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/xpos_mode/offlinesearch_customer', $storeId);
    }

    public function getSearchBy($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/xpos_mode/searching_by', $storeId);
    }

    public function getSearchStatus($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/xpos_mode/searching_status', $storeId);
    }

    public function getSearchingPruductTypes($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/xpos_mode/searching_product_types', $storeId);
    }

    public function getSearchingInStock($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/xpos_mode/searching_instock', $storeId);
    }

    public function getSearchingProductVisibility($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/xpos_mode/searching_product_visibility', $storeId);
    }

    public function getSearchingProductVisibilityFilter($storeId = null) {
        $json = $this->getSearchingProductVisibility();

        return json_encode($json);
    }

    public function getLuckSearch($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/xpos_mode/lucky_search', $storeId);
    }

    public function getNumberResult($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/xpos_mode/number_result', $storeId);
    }

    public function getAttributeForDisplay($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/xpos_mode/attribute_for_display', $storeId);
    }

    public function getOfflineMode($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/general/offline_mode', $storeId);
    }

    public function getCustPerRequest($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/xpos_mode/cust_per_request', $storeId);
    }

    public function getProdPerRequest($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/xpos_mode/prod_per_request', $storeId);
    }

    public function getEnableGuestCheckout($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/checkout/enable_guest_checkout', $storeId);
    }

    public function setEnableGuestCheckout($value) {
        return Mage::getConfig()->saveConfig('xpos/guest/enable_guest_checkout', $value);
    }


    public function getDefaultCustomerId($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/checkout/default_customer_id', $storeId);
    }

    public function getPrintReceipt($storeId = null) {

//        return 1;
//
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/receipt/turn_on_print_receipt', $storeId);
    }

    public function getLogoImageFile($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        return Mage::getStoreConfig('xpos/receipt/logo_image_file', $storeId);
    }

    public function getShowPrintGiftReceipt() {
        return true;
    }

    public function getShowAdditionInEndOfDayReport() {
        return false;
    }

    public function getCalculationTax() {
        return Mage::getStoreConfig('tax/calculation/apply_after_discount');
    }

    public function getConfigHideInfoIfIsZero($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }
        return Mage::getStoreConfig('xpos/receipt/hide_info_if_value_equal_zero', $storeId);
    }

    public function getApplyDiscountOn() {
        return Mage::getStoreConfig('tax/calculation/discount_tax');
    }

    //tax_cart_display_subtotal
    public function getDisplaySubtotal() {
        return Mage::getStoreConfig('tax/cart_display/subtotal');
    }

    public function getPriceIncludesTax() {
        return Mage::getStoreConfig('tax/calculation/price_includes_tax');
    }

    public function getCustomSalesEnable($storeId = null) {
        if ($storeId == null) {
            $storeId = $this->currentStoreId;
        }

        $enable = Mage::getStoreConfig('xpos/custom_sales/enabled', $storeId);
        if ($enable)
            Mage::getConfig()->saveConfig('xpos/checkout/onfly_discount', 1);
        return $enable;
    }
}
