<?php

class SM_XPayment_Block_Adminhtml_Xpayment_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("xpaymentGrid");
				$this->setDefaultSort("");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("xpayment-eav/xpayment")->getCollection()->addAttributeToSelect('*');
				$collection->getSelect()->distinct(true);
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("entity_id", array(
				"header" => Mage::helper("xpayment")->__("#"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "entity_id",
				));
                
				$this->addColumn("check_no", array(
				"header" => Mage::helper("xpayment")->__("Check #"),
				"index" => "check_no",
				));
				$this->addColumn("code", array(
				"header" => Mage::helper("xpayment")->__("Payment Method"),
				"index" => "code",
				));
				$this->addColumn("amount", array(
				"header" => Mage::helper("xpayment")->__("Amount"),
				"index" => "amount",
				));
			$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
			$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}


		
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('entity_id');
			$this->getMassactionBlock()->setFormFieldName('entity_ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_xpayment', array(
					 'label'=> Mage::helper('xpayment')->__('Remove XPayment'),
					 'url'  => $this->getUrl('*/adminhtml_xpayment/massRemove'),
					 'confirm' => Mage::helper('xpayment')->__('Are you sure?')
				));
			return $this;
		}
			

}
