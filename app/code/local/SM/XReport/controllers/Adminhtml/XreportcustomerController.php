<?php

class SM_XReport_Adminhtml_XreportcustomerController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("xreport/xreportcustomer")->_addBreadcrumb(Mage::helper("adminhtml")->__("Xreportcustomer  Manager"),Mage::helper("adminhtml")->__("Xreportcustomer Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("XReport"));
			    $this->_title($this->__("Manager Xreportcustomer"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("XReport"));
				$this->_title($this->__("Xreportcustomer"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("xreport/xreportcustomer")->load($id);
				if ($model->getId()) {
					Mage::register("xreportcustomer_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("xreport/xreportcustomer");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Xreportcustomer Manager"), Mage::helper("adminhtml")->__("Xreportcustomer Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Xreportcustomer Description"), Mage::helper("adminhtml")->__("Xreportcustomer Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("xreport/adminhtml_xreportcustomer_edit"))->_addLeft($this->getLayout()->createBlock("xreport/adminhtml_xreportcustomer_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("xreport")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

		$this->_title($this->__("XReport"));
		$this->_title($this->__("Xreportcustomer"));
		$this->_title($this->__("New Item"));

        $id   = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("xreport/xreportcustomer")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("xreportcustomer_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("xreport/xreportcustomer");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Xreportcustomer Manager"), Mage::helper("adminhtml")->__("Xreportcustomer Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Xreportcustomer Description"), Mage::helper("adminhtml")->__("Xreportcustomer Description"));


		$this->_addContent($this->getLayout()->createBlock("xreport/adminhtml_xreportcustomer_edit"))->_addLeft($this->getLayout()->createBlock("xreport/adminhtml_xreportcustomer_edit_tabs"));

		$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();


				if ($post_data) {

					try {

						

						$model = Mage::getModel("xreport/xreportcustomer")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Xreportcustomer was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setXreportcustomerData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setXreportcustomerData($this->getRequest()->getPost());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					return;
					}

				}
				$this->_redirect("*/*/");
		}



		public function deleteAction()
		{
				if( $this->getRequest()->getParam("id") > 0 ) {
					try {
						$model = Mage::getModel("xreport/xreportcustomer");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
						$this->_redirect("*/*/");
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					}
				}
				$this->_redirect("*/*/");
		}

		
		public function massRemoveAction()
		{
			try {
				$ids = $this->getRequest()->getPost('ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("xreport/xreportcustomer");
					  $model->setId($id)->delete();
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully removed"));
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}
			
		/**
		 * Export order grid to CSV format
		 */
		public function exportCsvAction()
		{
			$fileName   = 'xreportcustomer.csv';
			$grid       = $this->getLayout()->createBlock('xreport/adminhtml_xreportcustomer_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		} 
		/**
		 *  Export order grid to Excel XML format
		 */
		public function exportExcelAction()
		{
			$fileName   = 'xreportcustomer.xml';
			$grid       = $this->getLayout()->createBlock('xreport/adminhtml_xreportcustomer_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
		}
}
