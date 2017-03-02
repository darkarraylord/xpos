<?php

class SM_XPayment_Adminhtml_XpaymentController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("xpayment/xpayment")->_addBreadcrumb(Mage::helper("adminhtml")->__("XPayment  Manager"),Mage::helper("adminhtml")->__("XPayment Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("XPayment"));
			    $this->_title($this->__("Manager XPayment"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("XPayment"));
				$this->_title($this->__("XPayment"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("xpayment/xpayment")->load($id);
				if ($model->getId()) {
					Mage::register("xpayment_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("xpayment/xpayment");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("XPayment Manager"), Mage::helper("adminhtml")->__("XPayment Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("XPayment Description"), Mage::helper("adminhtml")->__("XPayment Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("xpayment/adminhtml_xpayment_edit"))->_addLeft($this->getLayout()->createBlock("xpayment/adminhtml_xpayment_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("xpayment")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

		$this->_title($this->__("XPayment"));
		$this->_title($this->__("XPayment"));
		$this->_title($this->__("New Item"));

        $id   = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("xpayment/xpayment")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("xpayment_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("xpayment/xpayment");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("XPayment Manager"), Mage::helper("adminhtml")->__("XPayment Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("XPayment Description"), Mage::helper("adminhtml")->__("XPayment Description"));


		$this->_addContent($this->getLayout()->createBlock("xpayment/adminhtml_xpayment_edit"))->_addLeft($this->getLayout()->createBlock("xpayment/adminhtml_xpayment_edit_tabs"));

		$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();


				if ($post_data) {

					try {

						

						$model = Mage::getModel("xpayment/xpayment")
						->addData($post_data)


						->setStoreId(1)
						->setId($this->getRequest()->getParam("id"))
						->save();

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("item was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setXPaymentData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setXPaymentData($this->getRequest()->getPost());
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
						$model = Mage::getModel("xpayment/xpayment");
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
				$ids = $this->getRequest()->getPost('entity_ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("xpayment/xpayment");
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
			$fileName   = 'xpayment.csv';
			$grid       = $this->getLayout()->createBlock('xpayment/adminhtml_xpayment_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		} 
		/**
		 *  Export order grid to Excel XML format
		 */
		public function exportExcelAction()
		{
			$fileName   = 'xpayment.xml';
			$grid       = $this->getLayout()->createBlock('xpayment/adminhtml_xpayment_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
		}
}
