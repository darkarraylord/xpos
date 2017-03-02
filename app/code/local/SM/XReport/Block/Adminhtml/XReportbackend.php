<?php

class SM_XReport_Block_Adminhtml_XReportbackend extends Mage_Adminhtml_Block_Template {

    public function getNumOfOrderInday() {
        $_a = $this->getDashboardHelper()->getNumberOfOrderInday();
        $_a= $_a['numOfOrder'];
        if (is_null($_a))
            return 0;
        else
            return $_a;
    }

    public function getSalesInday() {
        $_a = $this->getDashboardHelper()->getNumberOfOrderInday();
        $_a = $_a['revenue'];
        if (is_null($_a))
            return 0;
        else
            return Mage::helper('xreport')->_formatPrice($_a);
    }

    public function getAverageSalesInday() {
        $_a = $this->getDashboardHelper()->getNumberOfOrderInday();
        $_a = $_a['revenue'];
        $_b = $this->getDashboardHelper()->getNumberOfOrderInday();
        $_b = $_b['numOfOrder'];
        if (is_null($_b) || $_b == 0)
            return Mage::helper('xreport')->_formatPrice(0);
        else
            return Mage::helper('xreport')->_formatPrice($_a / $_b);
    }

    public function getQualityProductInday() {
        return $this->getDashboardHelper()->getQualityProductInday();
    }

    private $_dashboardHelper;

    private function getDashboardHelper() {
        if (is_null($this->_dashboardHelper))
            $this->_dashboardHelper = Mage::helper('xreport/dashboard_order');
        return $this->_dashboardHelper;
    }

    public function getDataStock() {

        $querry = 'SELECT SUM(`stock_status`) AS `in_stock`, COUNT(`stock_status`) AS `all_stock` FROM `cataloginventory_stock_status`';
        $collection = Mage::getModel('xreport/varien_data_collection')->getData($querry);
        $data = array(
            $collection[0]->getData('in_stock'),
            $collection[0]->getData('all_stock') - $collection[0]->getData('in_stock')
        );
        return json_encode($data);
    }

}
