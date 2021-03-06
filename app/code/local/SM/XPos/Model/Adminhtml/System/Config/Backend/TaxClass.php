<?php

/**
 * Created by mr.vjcspy@gmail.com/khoild@smartosc.com.
 * Date: 12/30/15
 * Time: 3:11 PM
 */
class SM_XPos_Model_Adminhtml_System_Config_Backend_TaxClass extends Mage_Core_Model_Config_Data {
    /**
     * Processing object after save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterSave() {
//        $taxClass = Mage::getStoreConfig('xpos/custom_sales/tax_class');
        $value = $this->getValue();
        if (is_null($value))
            Mage::throwException("You must chose Tax class for custom sales product!");
        else{
            $product = Mage::helper('xpos/customSalesHelper')->getCustomerSalesProduct();
            $product->setTaxClassId($this->getValue());
            $product->save();
        }
        return parent::_afterSave(); // TODO: Change the autogenerated stub
    }


}
