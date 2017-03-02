<?php

/**
 * Created by PhpStorm.
 * User: KhoiLe_vjcspy
 * Date: 6/23/15
 * Time: 4:17 PM
 */
class SM_XPos_Model_Iayz extends Mage_Core_Model_Abstract {

    const TYPE_PRODUCT_CONFIGURABLE = 'configurable';

    public function _construct() {
        parent::_construct();
        $this->_init('xpos/iayz');
    }

    public function getQuote() {
        /*can get many thing from current quote(grand total*/
        $session = Mage::getSingleton('adminhtml/session_quote');

        return $quote = $session->getQuote();
    }

    public function getQuoteId() {
        if ($this->getQuote() != null) {
            return $this->getQuote()->getId();
        } else {
            return null;
        }
    }

    public function getCurrentPaymentInQuote() {
        if ($this->getQuote() != null) {
            return $this->getQuote()->getPayment();
        } else {
            return null;
        }
    }

    public function getConfigDataPaymentMethod($code, $field) {
        $path = 'payment/' . $code . '/' . $field;

        return Mage::getStoreConfig($path);
    }

    /*TODO: CONFIG TO USE IN HELPER PRODUCT <affects to perfomance>*/

    public function ultraBootLoad() {
        if (Mage::getStoreConfig('xpos/xpos_mode/produc_load_type') == '0')
            return true;
        else
            return false;
    }

    public function getAddData() {
        if ($this->isMaxPerfomance())
            return true;

        return true;
    }

    public function isEnableManageStockbyMangento() {
        if ($this->isMaxPerfomance())
            return false;

        return true;
    }

    public function isMaxPerfomance() {
        return false;
    }


    /*________________________________________________________*/

    public function getCurrentStoreId() {
        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        $storeCode = (string)Mage::getSingleton('adminhtml/config_data')->getStore();
        $websiteCode = (string)Mage::getSingleton('adminhtml/config_data')->getWebsite();
        if ('' !== $storeCode) { // store level
            try {
                $storeId = Mage::getModel('core/store')->load($storeCode)->getId();
            } catch (Exception $ex) {
            }
        } elseif ('' !== $websiteCode) { // website level
            try {
                $storeId = Mage::getModel('core/website')->load($websiteCode)->getDefaultStore()->getId();
            } catch (Exception $ex) {
            }
        }

        return $storeId;
    }

    public function getCustomerTaxClassId($customer) {
        return $customer->getTaxClassId();
    }

    public function getProOp(Mage_Adminhtml_Controller_Action $controller, $product) {
        $options = null;
        Mage::helper('catalog/product')->setSkipSaleableCheck(true);
        Mage::unregister('current_product');
        Mage::unregister('product');
        Mage::register('current_product', $product);
        Mage::register('product', $product);
        $update = $controller->getLayout()->getUpdate();
        $update->resetHandles();
        $type = 'LAYOUT_GENERAL_CACHE_TAG';
        Mage::app()->getCacheInstance()->cleanType($type); // Clean cache //Mage::app()->cleanCache();
        $productType = $product->getTypeId();
        if ($product->getHasOptions() || $productType == "configurable" || $productType == "bundle"
            || $productType == "grouped" || $productType == "giftcard" || $productType == "giftvoucher"
        ) {
            $update->resetHandles();
            $update->addHandle('ADMINHTML_XPOS_CATALOG_PRODUCT_COMPOSITE_CONFIGURE');
            $update->addHandle('XPOS_PRODUCT_TYPE_' . $productType);
            $controller->loadLayoutUpdates()->generateLayoutXml()->generateLayoutBlocks();
            $options = $controller->getLayout()->getOutput();
            if (strlen($options) < 3) {
                $options = null;
            }
        }

        return $options;
    }

    public function getCurrentShippingTitle($_codeIn) {
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
        foreach ($methods as $_ccode => $_carrier) {
            if ($_methods = $_carrier->getAllowedMethods()) {
                foreach ($_methods as $_mcode => $_method) {
                    $_code = $_ccode . '_' . $_mcode;
                    if ($_codeIn == $_code) {
                        if ($_codeIn == 'xpayment_pickup_shipping_xpayment_pickup_shipping') {
                            return Mage::getStoreConfig('carriers/xpayment_pickup_shipping/title');
                        }

                        return $_method;
                    }
                }
            }
        }

        return '';
    }


    /*Timing script execution with microtime()*/
    public static function microtime_float() {
        list($usec, $sec) = explode(" ", microtime());

        return ((float)$usec + (float)$sec);
    }

    public static function startTime() {
        SM_XPos_Model_Iayz::setTimeStart(SM_XPos_Model_Iayz::microtime_float());
    }

    public static function timeExecution() {
        $timeStart = Mage::getSingleton('adminhtml/session')->getTimeStart();

//        if (!$timeStart) {
        return SM_XPos_Model_Iayz::microtime_float() - (float)SM_XPos_Model_Iayz::getTimeStart();
//        } else {
//            return null;
//        }
    }

    private static function setTimeStart($time) {
        Mage::getSingleton('adminhtml/session')->setTimeStart($time);
    }

    private static function getTimeStart() {
        Mage::getSingleton('adminhtml/session')->getTimeStart();
        Mage::getSingleton('adminhtml/session')->setTimeStart(null);
    }

//    end TIMING------------------------------------------------------------

    public function addLog($string) {
        Mage::log($string, NULL, 'iayz.log', true);
    }

    public function addLogCronjob($string) {
        Mage::log($string, null, 'xpos_cronjob.log', true);
    }

    public function getCurrentAdmin() {
        $currentAdmin = Mage::getSingleton('admin/session')->getUser();
        if ($currentAdmin == null) {
            return null;
        }
        $id = $currentAdmin->getId();
        $lastName = $currentAdmin->getLastname();
        $firstName = $currentAdmin->getFirstName();
        $email = $currentAdmin->getEmail();
        $getUserName = $currentAdmin->getUsername();

        return array(
            'id' => $id,
            'lastName' => $lastName,
            'firstName' => $firstName,
            'emai' => $email,
            'userName' => $getUserName,
        );
    }

    public function getManageStockByProductId($productId) {
//            $sProductId = 284;
        $oStockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
        if (!!$oStockItem->getData('use_config_manage_stock')) {
            if (!!Mage::getStoreConfig('cataloginventory/item_options/manage_stock')) {
                if (!!$isInstock = $oStockItem->getData('is_in_stock'))
                    $data = '11';
                else
                    $data = '10';
            } else
                $data = '0';
        } else {
            if (!!$oStockItem->getData('manage_stock')) {
                if (!!$isInstock = $oStockItem->getData('is_in_stock'))
                    $data = '11';
                else
                    $data = '10';
            } else {
                $data = '0';
            }
        }

        return $data;
    }


    public function getCategoryAction() {
        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addIsActiveFilter();
        foreach ($categories as $cata) {
            if ($cata->getParentCategories()->getId() == 2) {
            }
            echo $cata->getParentCategory()->getId() . ':' . $cata->getName() . '</br>';
        }
        die();
    }


    /*-------------------------------PRODUCT Configurable------------------------------------------------*/
    public function getProductSimpleFromConfiguarbleProduct() {
        $product = Mage::getModel('catalog/product')->load(877);
        $conf = Mage::getModel('catalog/product_type_configurable')->setProduct($product);

        $simple_collection = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
        $lstBundleId = array();
        foreach ($simple_collection as $simplePro) {
            $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($simplePro);
            $lstBundleId[$simplePro->getData('entity_id')] = $stock->getQty();
        }
        return $lstBundleId;
    }

    protected $_configurableProductsWithOptions = array();

    public function getChildOfConfigurableProductAttributeOption($id = 877, $allProduct = null) {
        if (!isset($this->_configurableProductsWithOptions[$id])) {
            /*
             * $allProduct difference null if xpos is integrating with another multiware house
             * if true, will check qty product
             * */
            if (!is_null($allProduct))
                $checkQty = false;

            /*TODO: gom het product co sku vao mot nhom voi cac option*/
            $product = Mage::getModel('catalog/product')->load($id);
            #Check if the product has children
            $options = array();
            if ($product->type_id == self::TYPE_PRODUCT_CONFIGURABLE) {
                // Get any super_attribute settings we need
                $productAttributesOptions = $product->getTypeInstance(true)->getConfigurableOptions($product);
                foreach ($productAttributesOptions as $productAttributeOption) {
                    $options[$product->getId()] = array();
                    foreach ($productAttributeOption as $optionValues) {
                        $val = $optionValues['option_title'];
                        $productId = $this->getCatalogModel()->getIdBySku($optionValues['sku']);
                        if (is_null($allProduct))
                            $qty = $this->getProductInvetoryModel()->loadByProduct($this->getCatalogModel()->load($productId))->getQty();
                        else {
                            if (isset($allProduct[$productId])) {
                                $checkQty = true;
                                $qty = $allProduct[$productId]['qty'];
                            } else
                                $qty = 0;
                        }
                        if (!isset($options[$product->getId()][$optionValues['sku']]))
                            $options[$product->getId()][$optionValues['sku']] = array();
                        $options[$product->getId()][$optionValues['sku']][$optionValues['attribute_code']] = $val;
                        $options[$product->getId()][$optionValues['sku']]['qty'] = $qty;
                        $options[$product->getId()][$optionValues['sku']]['id'] = $productId;
                        if(Mage::getStoreConfig('barcode/general/enabled')){
                            $childProduct = Mage::getModel('catalog/product')->load($productId);
                            $barcode = $childProduct->getData('sm_barcode');
                            $options[$product->getId()][$optionValues['sku']]['barcode'] = $barcode;
                        }
                    }
                }
            }
            if (!is_null($allProduct) && !$checkQty) // IDE will notify warning but this code always true
                $this->_configurableProductsWithOptions[$id] = false;
            else
                $this->_configurableProductsWithOptions[$id] = $options[$product->getId()];
        }
        return $this->_configurableProductsWithOptions[$id];
    }

    public function getAtrributeConfigurableProductByid($productId) {
        // load configurable product
        $product = Mage::getModel('catalog/product')->load($productId);

        // get simple produts' ids
//        $childIds = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($product->getId());

        // get simple products
//        $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts(null, $product);

        // get configurable options
        $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
        $attributeOptions = array();
        foreach ($productAttributeOptions as $productAttribute) {
            foreach ($productAttribute['values'] as $attribute) {
                $attributeOptions[$productAttribute['attribute_id'] . '_' . $productAttribute['attribute_code']][$attribute['value_index']] = $attribute['store_label'];
            }
        }
        return $attributeOptions;
        /*DO: return:
         array (
            '92_color' =>
                array (
                    20 => 'Black',
                    21 => 'Pink',
                ),
            '180_size' =>
                array (
                    78 => 'L',
                    79 => 'M',
                    80 => 'S',
                    81 => 'XS',
                    77 => 'XL',
                ),
        );*/
    }

    public function getAttributeIdbyCode($code = null) {
        if (is_null($code))
            $code = 'color';
        $attr = Mage::getModel('eav/entity_attribute')->getCollection()->addFieldToFilter('attribute_code', 'Color')->getFirstItem();

        $data = $attr->getData();
        return $data;
    }

    private $_catalogModel;

    public function getCatalogModel() {
        if (is_null($this->_catalogModel))
            $this->_catalogModel = Mage::getModel('catalog/product');
        return $this->_catalogModel;
    }

    private $_inventoryModel;

    public function getProductInvetoryModel() {
        if (is_null($this->_inventoryModel))
            $this->_inventoryModel = Mage::getModel('cataloginventory/stock_item');
        return $this->_inventoryModel;
    }

}
