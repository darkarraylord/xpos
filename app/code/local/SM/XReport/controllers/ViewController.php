<?php

class SM_XReport_ViewController extends Mage_Adminhtml_Controller_Action {
    protected $_publicActions = array('index', 'getDataChart', 'getMostView', 'getCustomersNewest', 'getBestCustomer', 'getTopSearch');

    public function indexAction() {

//        Mage::helper('xreport')->checkPhpVersion();

        if (Mage::getStoreConfig('xreport/general/enable') == 0) {
            $this->_redirect('adminhtml/system_config/edit/section/xreport');
        }
//        Mage::app()->getCacheInstance()->flush();
        $this->loadLayout();
        $this->_title($this->__("X-Report Dashboard"));
        $this->renderLayout();
    }

    public function postDataChartAction() {
        $dataRequet = $this->getRequest()->getParams();
        /*TODO: for test*/
        $test = false;
        if ($test) {
            $period = '7d';
            $filter = 0;
            $type = 'revenue';
        } else {
            $period = $dataRequet['period'];
            $filter = $dataRequet['filter'];
            $type = $dataRequet['type'];
        }

        $blockChart = $this->getLayout()->getBlockSingleton('xreport/adminhtml_dashboard_chart');
        $blockChart->setDataRow($type);
        $dataChart = $blockChart->getDataSalesChart($period, $filter);
        $this->getResponse()->setBody(json_encode($dataChart));
    }

    public function getMostViewAction() {
        if (Mage::getSingleton('xreport/session')->getData('store_id') != 0)
            $storeId = Mage::getSingleton('xreport/session')->getData('store_id');
        $collection = Mage::getResourceModel('reports/product_collection')
            ->addAttributeToSelect('*')
            ->addViewsCount()
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
            ->setCurPage(1)
            ->setPageSize(50);

        $arrayDataMostViewed = array();
        foreach ($collection as $c) {
            $arrayDataMostViewed[] = array(
                'name' => $c->getData('name'),
                'price' => $c->getData('price'),
                'views' => $c->getData('views'),
            );
        }
        $this->getResponse()->setBody(json_encode($arrayDataMostViewed));
    }

    public function getCustomersNewestAction() {
        $collection = Mage::getResourceModel('reports/customer_collection')
            ->addCustomerName();

        $storeFilter = 0;
        $storeId = null;
        if (Mage::getSingleton('xreport/session')->getData('store_id') != 0)
            $storeId = Mage::getSingleton('xreport/session')->getData('store_id');
        if (!is_null($storeId)) {
            $collection->addAttributeToFilter('store_id', $this->getRequest()->getParam('store'));
            $storeFilter = 1;
        }

        $collection->addOrdersStatistics($storeFilter)
            ->orderByCustomerRegistration()
            ->setCurPage(1)
            ->setPageSize(50);
        $arrayDataNewestCustomer = array();
        foreach ($collection as $c) {
            $arrayDataNewestCustomer[] = array(
                'name' => $c->getData('firstname') . ' ' . $c->getData('lastname'),
                'numofOrder' => $c->getData('orders_count'),
                'avg_amount' => Mage::helper('xreport')->_formatPrice($c->getData('orders_avg_amount')),
                'sum_amount' => Mage::helper('xreport')->_formatPrice($c->getData('orders_sum_amount'))
            );
        }
        $this->getResponse()->setBody(json_encode($arrayDataNewestCustomer));
    }

    public function getBestCustomerAction() {
        $collection = Mage::getResourceModel('reports/order_collection');
        /* @var $collection Mage_Reports_Model_Mysql4_Order_Collection */
        $collection
            ->groupByCustomer()
            ->addOrdersCount()
            ->joinCustomerName()
            ->setCurPage(1)
            ->setPageSize(50);

        $storeFilter = 0;
        $storeId = null;
        if (Mage::getSingleton('xreport/session')->getData('store_id') != 0)
            $storeId = Mage::getSingleton('xreport/session')->getData('store_id');
        if (!is_null($storeId)) {
            $collection->addAttributeToFilter('store_id', $this->getRequest()->getParam('store'));
            $storeFilter = 1;
        }
//        if ($this->getRequest()->getParam('store')) {
//            $collection->addAttributeToFilter('store_id', $this->getRequest()->getParam('store'));
//            $storeFilter = 1;
//        } else if ($this->getRequest()->getParam('website')) {
//            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
//            $collection->addAttributeToFilter('store_id', array('in' => $storeIds));
//        } else if ($this->getRequest()->getParam('group')) {
//            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
//            $collection->addAttributeToFilter('store_id', array('in' => $storeIds));
//        }

        $collection->addSumAvgTotals($storeFilter)
            ->orderByTotalAmount()
            ->setCurPage(1)
            ->setPageSize(50);
        $dataArrayBestCustomer = array();
        foreach ($collection as $c) {
            $dataArrayBestCustomer[] = array(
                'name' => $c->getData('customer_firstname') . ' ' . $c->getData('customer_lastname'),
                'numOfOrder' => $c->getData('orders_count'),
                'avg_amount' => Mage::helper('xreport')->_formatPrice($c->getData('orders_avg_amount')),
                'sum_amount' => Mage::helper('xreport')->_formatPrice($c->getData('orders_sum_amount'))
            );
        }
        $this->getResponse()->setBody(json_encode($dataArrayBestCustomer));
    }

    public function getTopSearchAction() {
        $collection = Mage::getModel('catalogsearch/query')
            ->getResourceCollection();

        if (Mage::getSingleton('xreport/session')->getData('store_id') != 0)
            $storeIds = Mage::getSingleton('xreport/session')->getData('store_id');
        else
            $storeIds = '';

        $collection
            ->setPopularQueryFilter($storeIds)
            ->setCurPage(1)
            ->setPageSize(50);
        $dataArrayTopSearch = array();
        foreach ($collection as $c) {
            $dataArrayTopSearch[] = array(
                'name' => $c->getData('name'),
                'num_results' => $c->getData('num_results'),
                'popularity' => $c->getData('popularity'),
            );
        }
        $this->getResponse()->setBody(json_encode($dataArrayTopSearch));
    }

    private function getXMainSession() {
        return Mage::getSingleton('xreport/session');
    }

    protected function _isAllowed()
    {
        return true;
    }
}
