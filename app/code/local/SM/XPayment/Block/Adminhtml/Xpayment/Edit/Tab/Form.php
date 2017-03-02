<?php
class SM_XPayment_Block_Adminhtml_Xpayment_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("xpayment_form", array("legend"=>Mage::helper("xpayment")->__("Item information")));

				
						$fieldset->addField("check_no", "text", array(
						"label" => Mage::helper("xpayment")->__("Check #"),
						"name" => "check_no",
						));
					
						$fieldset->addField("code", "text", array(
						"label" => Mage::helper("xpayment")->__("Payment Method"),
						"name" => "code",
						));
					
						$fieldset->addField("amount", "text", array(
						"label" => Mage::helper("xpayment")->__("Amount"),
						"name" => "amount",
						));
					

				if (Mage::getSingleton("adminhtml/session")->getXPaymentData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getXPaymentData());
					Mage::getSingleton("adminhtml/session")->setXPaymentData(null);
				} 
				elseif(Mage::registry("xpayment_data")) {
				    $form->setValues(Mage::registry("xpayment_data")->getData());
				}
				return parent::_prepareForm();
		}
}
