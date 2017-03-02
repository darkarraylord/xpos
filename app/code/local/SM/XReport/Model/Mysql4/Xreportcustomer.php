<?php
class SM_XReport_Model_Mysql4_Xreportcustomer extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("xreport/xreportcustomer", "id");
    }
}