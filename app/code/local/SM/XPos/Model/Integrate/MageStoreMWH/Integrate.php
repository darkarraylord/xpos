<?php

/**
 * Created by mr.vjcspy@gmail.com/khoild@smartosc.com.
 * Date: 1/18/16
 * Time: 10:46 AM
 */
class SM_XPos_Model_Integrate_MageStoreMWH_Integrate extends Mage_Core_Model_Abstract {

    /**
     * @var Magestore_Inventoryplus_Model_Warehouse_Product
     */
    protected $_warehouseProductModel;

    /**
     * SM_XPos_Model_Integrate_MageStoreMWH_Integrate constructor.
     */
    public function __construct() {
        if (Mage::helper('xpos/configXPOS')->getIntegrateXmwhEnable() == 2) {
            $this->_warehouseProductModel = Mage::getSingleton('inventoryplus/warehouse_product');
        }
        return parent::__construct();
    }

    /**
     * @return Magestore_Inventoryplus_Model_Warehouse_Product
     */
    public function getWarehouseProductModel() {
        return $this->_warehouseProductModel;
    }

    public function checkSalesAbleBundleProduct(array $childrenIds, $allProduct) {
        $flag = false;
        foreach ($childrenIds as $k => $items) {
            foreach ($items as $productId) {
                if (array_key_exists($productId, $allProduct))
                    return true;
            }
        }
        return $flag;
    }

    public function checkSalesAbleGroupedProduct(array $associatedProduct, $allProduct) {
        $flag = false;
        foreach ($associatedProduct as $product) {
            /* $var $product Mage_Catalog_Model_Product*/
            if (array_key_exists($product->getId(), $allProduct))
                return true;
        }
        return $flag;
    }

}
