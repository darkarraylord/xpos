<?php

class SM_XPayment_Model_Resource_Eav_Mysql4_Xpayment_Collection extends Mage_Eav_Model_Entity_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('xpayment-eav/xpayment', 'xpayment-eav/xpayment');
    }

}
