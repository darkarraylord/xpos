<?php
class SM_XPayment_Block_Adminhtml_Xpayment_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
		public function __construct()
		{
				parent::__construct();
				$this->setId("xpayment_tabs");
				$this->setDestElementId("edit_form");
				$this->setTitle(Mage::helper("xpayment")->__("Item Information"));
		}
		protected function _beforeToHtml()
		{
				$this->addTab("form_section", array(
				"label" => Mage::helper("xpayment")->__("Item Information"),
				"title" => Mage::helper("xpayment")->__("Item Information"),
				"content" => $this->getLayout()->createBlock("xpayment/adminhtml_xpayment_edit_tab_form")->toHtml(),
				));
				return parent::_beforeToHtml();
		}

}
