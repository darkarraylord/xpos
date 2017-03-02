<?php

/**
 * Created by PhpStorm.
 * User: vjcspy
 * Date: 6/23/15
 * Time: 4:56 PM
 */
class SM_XPos_Block_Adminhtml_Config_Frontend_ListCustomer extends Mage_Core_Block_Template
{


    public function getCurrentStoreId()
    {
        $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        $storeCode = (string)Mage::getSingleton('adminhtml/config_data')->getStore();
        $websiteCode = (string)Mage::getSingleton('adminhtml/config_data')->getWebsite();
        if ('' !== $storeCode) { // store level
            try {
                $storeId = Mage::getModel('core/store')->load( $storeCode )->getId();
            } catch (Exception $ex) {  }
        } elseif ('' !== $websiteCode) { // website level
            try {
                $storeId = Mage::getModel('core/website')->load( $websiteCode )->getDefaultStore()->getId();
            } catch (Exception $ex) {  }
        }
        return $storeId;
    }
}
