<?php

/**
 * Created by mr.vjcspy@gmail.com/khoild@smartosc.com.
 * Date: 1/18/16
 * Time: 5:00 PM
 */
class SM_XPos_Helper_Integrate_Inventorywarehouse_Warehouse extends Magestore_Inventorywarehouse_Helper_Warehouse {

    /**
     * @var SM_XPos_Helper_Data
     */
    protected $_xposHelperData;
    /**
     * @var SM_XPos_Helper_ConfigXPOS
     */
    protected $_xposConfig;

    /**
     * SM_XPos_Helper_Integrate_Inventorywarehouse_Warehouse constructor.
     */
    public function __construct() {
        $this->_xposHelperData = Mage::helper('xpos');
        $this->_xposConfig = Mage::helper('xpos/configXPOS');
    }


    /**
     * @param $storeId
     * @param $productId
     * @param $selectWarehouse
     * @param $ShippingAddress
     * @param $billingAddress
     * @return mixed|string
     */
    public function getQtyProductWarehouse($storeId, $productId, $selectWarehouse, $ShippingAddress, $billingAddress) {
        if ($this->_xposConfig->getIntegrateXmwhEnable() === '2')
            return $warehouseId = Mage::getSingleton('adminhtml/session')->getWarehouseId();
        else
            return parent::getQtyProductWarehouse($storeId, $productId, $selectWarehouse, $ShippingAddress, $billingAddress);
    }
}
