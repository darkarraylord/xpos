<?php

class SM_XPayment_Model_Xpayment extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {

        $this->_init("xpayment-eav/xpayment");

    }

    public function isEnable()
    {
        $config = Mage::getStoreConfig('xpayment/xpaymentMultiple/active');
        if (!!$config) {
            return true;
        } else {
            return false;
        }
    }
}
