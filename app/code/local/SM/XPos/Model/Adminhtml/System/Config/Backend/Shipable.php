<?php

/**
 * Created by mr.vjcspy@gmail.com/khoild@smartosc.com.
 * Date: 12/30/15
 * Time: 3:59 PM
 */
class SM_XPos_Model_Adminhtml_System_Config_Backend_Shipable extends Mage_Core_Model_Config_Data {
    protected function _afterSave() {
        $value = $this->getValue();
        $product = Mage::helper('xpos/customSalesHelper')->getCustomerSalesProduct();
        if (!!$value)
            $product->setTypeId('simple');
        else
            $product->setTypeId('virtual');

        $product->save();
    }
}
