<?php

/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 11/2/15
 * Time: 10:22 AM
 */
class SM_XReport_SalesController extends Mage_Adminhtml_Controller_Action {
    protected $_publicActions = array('index', 'viewHistory', 'viewDetail');

    public function viewHistoryAction() {
        $this->loadLayout();
        $this->_title($this->__("X-Report Sales History"));
        $this->renderLayout();
    }

    public function viewDetailAction() {
        $this->loadLayout();
        $this->_title($this->__("X-Report Sales Detail"));
        $this->renderLayout();
    }

    public function viewInventoryAction() {
        $this->loadLayout();
        $this->_title($this->__("X-Report Inventory"));
        $this->renderLayout();
    }

    public function getDataCollectionAction() {
        $data = $this->getRequest()->getParams();
        $block = $this->getLayout()->getBlockSingleton('xreport/adminhtml_sales_history_grid');

        //Set data DataTable
        $block->setDataDbTable($data);

        //init Collection
        $isInited = false;
        foreach ($data['columns'] as $col) {
            if ($col['search']['value'] != '' && $col['data'] == "created_at") {
                $range = $col['search']['value'];
                $array = explode('/', $range);
                $customStart = $array[0];
                $customEnd = $array[1];
                $block->initCollection($customStart, $customEnd);
                $isInited = true;
                break;
            }
        }
        if (!$isInited)
            $block->initCollection();

        //get data From Collection
        $dataResult = $block->getDataCollection();

        $this->getResponse()->setBody(json_encode($dataResult));
    }

    public function getDataSalesDetailCollectionAction() {
        $data = $this->getRequest()->getParams();
        $block = $this->getLayout()->getBlockSingleton('xreport/adminhtml_sales_detail_grid');

        //Set data DataTable
        $block->setDataDbTable($data);

        //init Collection
        $isInited = false;
        foreach ($data['columns'] as $col) {
            if ($col['search']['value'] != '' && $col['data'] == "created_at") {
                $range = $col['search']['value'];
                $array = explode('/', $range);
                $customStart = $array[0];
                $customEnd = $array[1];
                $block->initCollection($customStart, $customEnd);
                $isInited = true;
                break;
            }
        }

        if (!$isInited)
            $block->initCollection();
        //get data From Collection
        $dataResult = $block->getDataCollection();

        $this->getResponse()->setBody(json_encode($dataResult));
    }

    public function getDataInventoryCollectionAction() {
        $data = $this->getRequest()->getParams();
        $block = $this->getLayout()->getBlockSingleton('xreport/adminhtml_sales_inventory_grid');

        //Set data DataTable
        $block->setDataDbTable($data);

        //init Collection
        $isInited = false;
        foreach ($data['columns'] as $col) {
            if ($col['search']['value'] != '' && $col['data'] == "created_at") {
                $range = $col['search']['value'];
                $array = explode('/', $range);
                $customStart = $array[0];
                $customEnd = $array[1];
                $block->initCollection($customStart, $customEnd);
                $isInited = true;
                break;
            }
        }
        if (!$isInited)
            $block->initCollection();

        //get data From Collection
        $dataResult = $block->getDataCollection();

        $this->getResponse()->setBody(json_encode($dataResult));
    }
    
    protected function _isAllowed()
    {
        return true;
    }
}
