<?php

class SM_XPayment_Model_Resource_Eav_Mysql4_Xpayment extends Mage_Eav_Model_Entity_Abstract
{

    public function _construct()
    {
        $resource = Mage::getSingleton('core/resource');
        $this->setType('sp205');
        $this->setConnection(
            $resource->getConnection('xpayment-eav_read'),
            $resource->getConnection('xpayment-eav_write')
             );
    }

}
