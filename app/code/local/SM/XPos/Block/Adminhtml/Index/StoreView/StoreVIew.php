<?php
/**
 * Created by PhpStorm.
 * User: vjcspy
 * Date: 6/26/15
 * Time: 3:10 PM
 */
class SM_XPos_Block_Adminhtml_Index_StoreView_StoreVIew extends Mage_Core_Block_Template{
    public $_options;
    public function loadTill(){
        if(Mage::helper('xpos/configXPOS')->getIntegrateXmwhEnable() === 1){
            $warehouse_id = Mage::getSingleton('adminhtml/session')->getWarehouseId();
            $collection = Mage::getModel('xpos/till')->getCollection()
                ->addFieldToFilter('warehouse_id',$warehouse_id)
                ->addFieldToFilter('is_active',1);
        }else{
            if(Mage::helper('xpos/configXPOS')->getIntegrateXmwhEnable() == "2"){
                $warehouse_id = Mage::getSingleton('inventoryxpos/session')->getWarehouseId();
                $collection = Mage::getModel('xpos/till')->getCollection()
                    ->addFieldToFilter('warehouse_id',$warehouse_id)
                    ->addFieldToFilter('is_active',1);
            } else {
                $collection = Mage::getModel('xpos/till')->getCollection()
                    ->addFieldToFilter('is_active', 1);
            }
        }

        return $collection;
    }

    public function loadStoreView()
    {
        return $this->_options = Mage::getResourceModel('core/store_collection')
            ->addFieldToFilter('is_active', 1)
            ->load()->toOptionArray();
    }

}
