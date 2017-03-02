<?php
class SM_XReport_Block_Adminhtml_Xreportcustomer_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("xreport_form", array("legend"=>Mage::helper("xreport")->__("Item information")));

				
						$fieldset->addField("id", "text", array(
						"label" => Mage::helper("xreport")->__("Id"),
						"name" => "id",
						));
					
						$dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(
							Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
						);

						$fieldset->addField('date', 'date', array(
						'label'        => Mage::helper('xreport')->__('Date'),
						'name'         => 'date',
						'time' => true,
						'image'        => $this->getSkinUrl('images/grid-cal.gif'),
						'format'       => $dateFormatIso
						));
						$fieldset->addField("num_of_new_customer", "text", array(
						"label" => Mage::helper("xreport")->__("Num of new customer"),
						"name" => "num_of_new_customer",
						));
					

				if (Mage::getSingleton("adminhtml/session")->getXreportcustomerData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getXreportcustomerData());
					Mage::getSingleton("adminhtml/session")->setXreportcustomerData(null);
				} 
				elseif(Mage::registry("xreportcustomer_data")) {
				    $form->setValues(Mage::registry("xreportcustomer_data")->getData());
				}
				return parent::_prepareForm();
		}
}
