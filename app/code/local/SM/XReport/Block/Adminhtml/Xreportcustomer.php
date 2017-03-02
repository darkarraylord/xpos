<?php


class SM_XReport_Block_Adminhtml_Xreportcustomer extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_xreportcustomer";
	$this->_blockGroup = "xreport";
	$this->_headerText = Mage::helper("xreport")->__("Xreportcustomer Manager");
	$this->_addButtonLabel = Mage::helper("xreport")->__("Add New Item");
	parent::__construct();
	
	}

}