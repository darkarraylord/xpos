<?php


class SM_XPayment_Block_Adminhtml_Xpayment extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_controller = "adminhtml_xpayment";
        $this->_blockGroup = "xpayment";
        $this->_headerText = Mage::helper("xpayment")->__("XPayment Manager");
        $this->_addButtonLabel = Mage::helper("xpayment")->__("Add New Item");
        parent::__construct();

    }

}
