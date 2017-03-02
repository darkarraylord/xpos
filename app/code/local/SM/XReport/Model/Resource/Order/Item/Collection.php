<?php

/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 11/17/15
 * Time: 9:59 AM
 */
class SM_XReport_Model_Resource_Order_Item_Collection extends SM_XReport_Model_Resource_Order_Collection {
    public function getOrderItemCollection($range = 'custom', $customStart, $customEnd, $isFilter = 0) {
        $this->setMainTable('sales/order_item');

        /**
         * Reset all columns, because result will group only by 'created_at' field
         */
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS);


        $dateRange = $this->getDateRange($range, $customStart, $customEnd);


        $this->addFieldToFilter('main_table.created_at', $dateRange);

        return $this;
    }
}
