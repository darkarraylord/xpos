<?php

class SM_XReport_Block_Adminhtml_Xreportcustomer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("xreportcustomerGrid");
				$this->setDefaultSort("id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("xreport/xreportcustomer")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("id", array(
				"header" => Mage::helper("xreport")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "id",
				));
                
					$this->addColumn('date', array(
						'header'    => Mage::helper('xreport')->__('Date'),
						'index'     => 'date',
						'type'      => 'datetime',
					));
				$this->addColumn("num_of_new_customer", array(
				"header" => Mage::helper("xreport")->__("Num of new customer"),
				"index" => "num_of_new_customer",
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
			$this->setMassactionIdField('id');
			$this->getMassactionBlock()->setFormFieldName('ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_xreportcustomer', array(
					 'label'=> Mage::helper('xreport')->__('Remove Xreportcustomer'),
					 'url'  => $this->getUrl('*/adminhtml_xreportcustomer/massRemove'),
					 'confirm' => Mage::helper('xreport')->__('Are you sure?')
				));
			return $this;
		}
			

}