<?php

/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 10/21/15
 * Time: 4:03 PM
 */
class SM_XReport_Helper_Dashboard_Order extends Mage_Adminhtml_Helper_Dashboard_Abstract {

    const _PeriodInDay = '24h';

    protected function _initCollection() {
        $isFilter = $this->getParam('store') || $this->getParam('website') || $this->getParam('group');

        $this->_collection = Mage::getResourceSingleton('reports/order_collection')
            ->prepareSummary($this->getParam('period'), 0, 0, $isFilter);

        $this->addStoreToFilter();

        if ($this->getParam('store')) {
            $this->_collection->addFieldToFilter('store_id', $this->getParam('store'));
        } else if ($this->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
            $this->_collection->addFieldToFilter('store_id', array('in' => implode(',', $storeIds)));
        } else if ($this->getParam('group')) {
            $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
            $this->_collection->addFieldToFilter('store_id', array('in' => implode(',', $storeIds)));
        } elseif (!$this->_collection->isLive()) {
            $this->_collection->addFieldToFilter('store_id',
                array('eq' => Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId())
            );
        }


        $this->_collection->load();
    }

    public function getNumberOfOrderInday($store = null, $website = null, $group = null) {
        $isFilter = $store || $website || $group;

        $this->_collection = Mage::getResourceSingleton('reports/order_collection')
            ->prepareSummary(SM_XReport_Helper_Dashboard_Order::_PeriodInDay, 0, 0, $isFilter);

        $this->addStoreToFilter();

        if ($this->getParam('store')) {
            $this->_collection->addFieldToFilter('store_id', $this->getParam('store'));
        } else if ($this->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
            $this->_collection->addFieldToFilter('store_id', array('in' => implode(',', $storeIds)));
        } else if ($this->getParam('group')) {
            $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
            $this->_collection->addFieldToFilter('store_id', array('in' => implode(',', $storeIds)));
        } elseif (!$this->_collection->isLive()) {
            $this->_collection->addFieldToFilter('store_id',
                array('eq' => Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId())
            );
        }


//        $order = $this->getCollection()->getFirstItem();
        $numOfOrder = 0;
        $revenue = 0;
        foreach ($this->getCollection() as $o) {
            $numOfOrder += $o->getData('quantity');
            $revenue += $o->getData('revenue');
        }

//        $numOfOrder = $order->getData('quantity');
//        $revenue = $order->getData('revenue');
        return array(
            'numOfOrder' => $numOfOrder,
            'revenue' => $revenue,
        );
    }

    public function getQualityProductInday($store = null, $website = null, $group = null) {
        $isFilter = $store || $website || $group;
        $this->_collection = Mage::getModel('xreport/resource_order_collection')->calProductInday('24h', 0, 0, $isFilter);

        $this->addStoreToFilter();

        /*TODO: FIXME: must filter by store*/
//        if ($this->getParam('store')) {
//            $this->_collection->addFieldToFilter('store_id', $this->getParam('store'));
//        } else if ($this->getParam('website')) {
//            $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
//            $this->_collection->addFieldToFilter('store_id', array('in' => implode(',', $storeIds)));
//        } else if ($this->getParam('group')) {
//            $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
//            $this->_collection->addFieldToFilter('store_id', array('in' => implode(',', $storeIds)));
//        } elseif (!$this->_collection->isLive()) {
//            $this->_collection->addFieldToFilter('store_id',
//                array('eq' => Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId())
//            );
//        }
        $totalProduct = 0;
        foreach ($this->getCollection() as $oI) {
            $totalProduct += $oI->getData('quality_product');
        }

        return round($totalProduct, 2);
    }

    /**
     * @param string $range
     * @param $customStart
     * @param $customEnd
     * @param int $isFilter
     * @return $this
     */
    public function getSalesHistoryCollection($range = 'custom', $customStart, $customEnd, $isFilter = 0) {
        $this->_collection = Mage::getModel('xreport/resource_order_collection')->getOrderSalesHistoryCollection($range, $customStart, $customEnd);
        return $this->_collection;
    }


    /*TODO: ARRAY DATA FOR SELECT*/
    public function getArrayOrderStatusForSelect() {
        $orderStatusCollection = Mage::getModel('sales/order_status')->getResourceCollection()->getData();
        $status = array();
        $status[] = array(
            'label' => 'Please Select..',
            'value' => ''
        );

        foreach ($orderStatusCollection as $orderStatus) {
            $status[] = array(
                'value' => $orderStatus['status'], 'label' => $orderStatus['label']
            );
        }
        return Mage::helper('core')->jsonEncode($status);
    }

    private function addStoreToFilter() {
        if (Mage::getSingleton('xreport/session')->getData('store_id') != 0)
            $this->_collection->addFieldToFilter('store_id', Mage::getSingleton('xreport/session')->getData('store_id'));
    }

    /*TODO: END ARRAY DATA FOR SELECT*/
}
