<?php

class SM_Xpos_Block_Adminhtml_Index_Warehouse_Listwarehouse extends Mage_Core_Block_Template {

    const CACHE_KEY_WAREHOUSE = 'ware_house';

    public function loadWarehouse() {
        $currentUserId = Mage::getSingleton('admin/session')->getUser()->getId();
        $currentStoreId = Mage::helper('xpos/product')->getCurrentSessionStoreId();

        // key dependency
        $key = array(
            'user_id' => $currentUserId,
            'store_id' => $currentStoreId
        );
        $xposSession = $this->getXPosSession();
        if (!$xposSession->hasCache($key)) {
            if (Mage::getStoreConfig('xpos/general/integrate_xmwh_enabled') == 1) {
                $allowWarehouse = Mage::getModel('xwarehouse/user')->getCollection();
                $allowWarehouse->getSelect()->join(Mage::getConfig()->getTablePrefix() . 'sm_warehouses', 'main_table.warehouse_id =' . Mage::getConfig()->getTablePrefix() . 'sm_warehouses.warehouse_id', array('label'));
                $allowWarehouse->addFieldToFilter('user_id', array('eq' => $currentUserId));
                $xposSession->setCache($key, $allowWarehouse);
            } elseif (!!Mage::getStoreConfig('xpos/general/integrate_xmwh_enabled')) {
//                $webSiteId = Mage::getModel('core/store')->load($currentStoreId)->getGroupId();
                /*get all ware house*/
                $allWareHouseEnableWithName = Mage::helper('inventorywarehouse/warehouse')->getAllWarehouseNameEnable();
                $wareHouseResult = array();
                foreach ($allWareHouseEnableWithName as $k => $item) {
                    $object = new Varien_Object();
                    $object->setData('warehouse_id', $k);
                    $object->setLabel($item);
                    $wareHouseResult[] = $object;
                }
                $xposSession->setCache($key, $wareHouseResult);
            }
        }
        return $xposSession->getCache($key);
    }

    /**
     * @return SM_XPos_Model_Session;
     */
    private function getXPosSession() {
        return Mage::getSingleton('xpos/session');
    }
}
