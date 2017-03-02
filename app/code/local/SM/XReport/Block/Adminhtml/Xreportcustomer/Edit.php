<?php
	
class SM_XReport_Block_Adminhtml_Xreportcustomer_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "id";
				$this->_blockGroup = "xreport";
				$this->_controller = "adminhtml_xreportcustomer";
				$this->_updateButton("save", "label", Mage::helper("xreport")->__("Save Item"));
				$this->_updateButton("delete", "label", Mage::helper("xreport")->__("Delete Item"));

				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("xreport")->__("Save And Continue Edit"),
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
				if( Mage::registry("xreportcustomer_data") && Mage::registry("xreportcustomer_data")->getId() ){

				    return Mage::helper("xreport")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("xreportcustomer_data")->getId()));

				} 
				else{

				     return Mage::helper("xreport")->__("Add Item");

				}
		}
}