<?php

/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 10/21/15
 * Time: 3:02 PM
 */
class SM_XReport_Model_Resource_Order_Collection extends Mage_Reports_Model_Resource_Order_Collection {


    public function calProductInday($range = '24h', $customStart, $customEnd, $isFilter = 0) {
        $this->setMainTable('sales/order_item');
        $adapter = $this->getConnection();

        /**
         * Reset all columns, because result will group only by 'created_at' field
         */
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS);

        $expression = $this->_getQualityProductExpression();
        $this->getSelect()->columns(array(
            'quality_product' => new Zend_Db_Expr(sprintf('SUM(%s)', $expression))
        ));

        $dateRange = $this->getDateRange($range, $customStart, $customEnd);

        $tzRangeOffsetExpression = $this->_getTZRangeOffsetExpression(
            $range, 'created_at', $dateRange['from'], $dateRange['to']
        );

        $this->getSelect()
            ->columns(array(
                'quantity' => 'COUNT(main_table.order_id)',
                'range' => $tzRangeOffsetExpression,
            ))
//            ->where('main_table.state NOT IN (?)', array(
//                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
//                    Mage_Sales_Model_Order::STATE_NEW)
//            )
            ->order('range', Zend_Db_Select::SQL_ASC)
            ->group($tzRangeOffsetExpression);

        $this->addFieldToFilter('created_at', $dateRange);

        return $this;
    }


    private $_qualityProductExpression;

    /**
     * @return string
     */
    protected function _getQualityProductExpression() {
        if (is_null($this->_qualityProductExpression)) {
            $adapter = $this->getConnection();
            $expressionTransferObject = new Varien_Object(array(
                'expression' => '%s - %s',
                'arguments' => array(
                    $adapter->getIfNullSql('main_table.qty_invoiced', 0),
                    $adapter->getIfNullSql('main_table.qty_refunded', 0),
                )
            ));

            Mage::dispatchEvent('quality_product_expression', array(
                'collection' => $this,
                'expression_object' => $expressionTransferObject,
            ));
            $this->_qualityProductExpression = vsprintf(
                $expressionTransferObject->getExpression(),
                $expressionTransferObject->getArguments()
            );
        }

        return $this->_qualityProductExpression;
    }

    /**
     * @param string $range
     * @param $customStart
     * @param $customEnd
     * @param int $isFilter
     * @return $this
     */
    public function getOrderSalesHistoryCollection($range = 'custom', $customStart, $customEnd, $isFilter = 0) {
        $this->setMainTable('sales/order');
        $adapter = $this->getConnection();

        /**
         * Reset all columns, because result will group only by 'created_at' field
         */
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS);


        $dateRange = $this->getDateRange($range, $customStart, $customEnd);

//        $tzRangeOffsetExpression = $this->_getTZRangeOffsetExpression(
//            $range, 'created_at', $dateRange['from'], $dateRange['to']
//        );

//        $this->getSelect()
//            ->where($this->_getConditionSql('main_table.created_at', $this->getDateRange($range, $customStart, $customEnd)));

        $this->addFieldToFilter('main_table.created_at', $dateRange);

        return $this;
    }
}
