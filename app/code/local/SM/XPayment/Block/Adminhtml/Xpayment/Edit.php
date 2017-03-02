<?php
	
class SM_XPayment_Block_Adminhtml_Xpayment_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "";
				$this->_blockGroup = "xpayment";
				$this->_controller = "adminhtml_xpayment";
				$this->_updateButton("save", "label", Mage::helper("xpayment")->__("Save Item"));
				$this->_updateButton("delete", "label", Mage::helper("xpayment")->__("Delete Item"));

				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("xpayment")->__("Save And Continue Edit"),
					"onclick"   => "saveAndContinueEdit()",
					"class"     => "save",
				), -100);



				$this->_formScripts[] = "

							function saveAndContinueEdit(){
								editForm.submit($('edit_form').action+'back/edit/');
							}
						";
		}

		public function getHeaderText()
		{
				if( Mage::registry("xpayment_data") && Mage::registry("xpayment_data")->getId() ){

				    return Mage::helper("xpayment")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("xpayment_data")->getId()));

				} 
				else{

				     return Mage::helper("xpayment")->__("Add Item");

				}
		}
}
