<?php

/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 11/3/15
 * Time: 5:08 PM
 */
class SM_XReport_Block_DataTable_DataTableBlockAbstract extends Mage_Adminhtml_Block_Template {
    private $_dataTable;

    public function setDataDbTable($data) {
        $this->_dataTable = $data;
    }

    public function getDataDbTable($key) {
        if (isset($this->_dataTable[$key]))
            return $this->_dataTable[$key];
        else
            return null;
    }

    public function sortCollection($collection, $filter, $column) {
        $columnName = $column[$filter['column']]['data'];
        $dir = $filter['dir'];
        $zSort = null;
        $a = $this->getDataDbTable('search');
        /*FIXME: hard code : truong hop nay thuc te khong bao h xay ra do la sort by sku va sap xep lai theo method*/
        if ($a['value'] != '' && $a['value'] != $columnName && in_array($columnName, array(
                'method', 'sku', 'customer_group_code', 'customer_email', 'product_type', 'order_currency_code', 'day_of_week', 'hour', 'status', 'shipping_method'
            ))
        )
            return $collection;
        switch ($dir) {
            case 'desc':
                $zSort = Zend_Db_Select::SQL_DESC;
                break;
            case 'asc':
                $zSort = Zend_Db_Select::SQL_ASC;
                break;
        }
        $collection->setOrder($columnName, $zSort);
        return $collection;
    }

    public function filterByColumn($collection, $arrayDataFillter) {
        $exactly = '/^".*"$/';
        $first = '/^\^.*/';

        $grand = '/(^>)=?\d*/';
        $less = '/(^<)=?\d*/';
        $eq = '/(^=)\d*/';
        $table = array(
            'sales_flat_order_item' => Mage::helper('xreport/sql_data')->getTable('sales_flat_order_item'),
        );
        foreach ($arrayDataFillter as $col) {
            if ($col['search']['value'] != '') {

                $columnName = $col['data'];
                if ($columnName != 'method')
                    $columnName = 'main_table.' . $columnName;
                // Loai created_at boi vi da filter o ben ngoai
                if ($columnName == 'main_table.created_at')
                    continue;
                if ($columnName == 'main_table.sku') {
                    $columnName = 'sku';
                    $collection->getSelect()->joinLeft(array('odi' => $table['sales_flat_order_item']),
                        'odi.order_id=main_table.entity_id', array('odi.sku'));
                    $collection->getSelect()->group('entity_id');
                }
                $searchValue = $col['search']['value'];
                if (preg_match($first, $searchValue)) {
                    $names = str_replace('^', '', $searchValue);
                    $searchValue = $names . '%';
                    $collection->addFieldToFilter($columnName, array('like' => $searchValue));
                } else if (preg_match($exactly, $searchValue)) {
                    $searchValue = str_replace('"', '', $searchValue);
                    $collection->addFieldToFilter($columnName, array('eq' => $searchValue));
                } else if (preg_match($grand, $searchValue)) {
                    $searchValue = str_replace('>', '', $searchValue);
                    $collection->addFieldToFilter($columnName, array('gteq' => $searchValue));
                } else if (preg_match($less, $searchValue)) {
                    $searchValue = str_replace('<', '', $searchValue);
                    $collection->addFieldToFilter($columnName, array('lteq' => $searchValue));
                } else if (preg_match($eq, $searchValue)) {
                    $searchValue = str_replace('=', '', $searchValue);
                    $collection->addFieldToFilter($columnName, array('eq' => $searchValue));
                } else {
                    /*TODO: first*/
                    $searchValue = '%' . $searchValue . '%';
                    $collection->addFieldToFilter($columnName, array('like' => $searchValue));
                }
            }
        }
        return $collection;
    }

    public function getBlockHelper() {
        return Mage::helper('xreport/dashboard_order');
    }

    public function __call($method, $args) {
        $helper = $this->getBlockHelper();
        if (method_exists($helper, $method)) {
            return call_user_func_array(array($helper, $method), $args);
        }

        return parent::__call($method, $args);
    }

    public $_collection;

    /**
     * TODO: run after setfilter, if not, will collect admin store
     * @param $customStart
     * @param $customEnd
     */
    public function initCollection($customStart = null, $customEnd = null) {
//        Mage::app()->getLocale()->setLocaleCode('en_US');

        if ($customStart == null || $customEnd == null) {
            $customEnd = Mage::app()->getLocale()->date();
            $customStart = clone $customEnd;

            // go to the end of a day
            $customEnd->setHour(23);
            $customEnd->setMinute(59);
            $customEnd->setSecond(59);

            $customStart->setHour(0);
            $customStart->setMinute(0);
            $customStart->setSecond(0);
            $customStart->subDay(6);
        } else {

//            $customStart = Mage::app()->getLocale()->storeDate(Mage::getSingleton('xreport/session')->getData('store_id'), $customStart, null, 'YYYY-MM-dd');
//            $customEnd = Mage::app()->getLocale()->storeDate(Mage::getSingleton('xreport/session')->getData('store_id'), $customEnd, null, 'YYYY-MM-dd');

            $customEnd = Mage::app()->getLocale()->date($customEnd, null, null, false);
            $customStart = Mage::app()->getLocale()->date($customStart, null, null, false);

            $customEnd->setHour(23);
            $customEnd->setMinute(59);
            $customEnd->setSecond(59);

            $customStart->setHour(0);
            $customStart->setMinute(0);
            $customStart->setSecond(0);
        }
        $collection = $this->getBlockHelper()->getSalesHistoryCollection('custom', $customStart, $customEnd);
        if (Mage::getSingleton('xreport/session')->getData('store_id') != 0)
            $collection->addFieldToFilter('store_id', Mage::getSingleton('xreport/session')->getData('store_id'));

        $this->setCollection($collection);
    }

    public function setCollection($collection) {
        $this->_collection = $collection;
    }

    public function getDataCollection() {
        $collection = $this->_collection->addAttributeToSelect('*');
        $table = array(
            'sales_flat_order_payment' => Mage::helper('xreport/sql_data')->getTable('sales_flat_order_payment'),
        );
//        $collection->join(array('payment' => 'sales/order_payment'), 'main_table.entity_id=payment.parent_id', 'method', null, 'left');
        $collection->getSelect()->joinLeft(array('a' => $table['sales_flat_order_payment']), 'a.parent_id=main_table.entity_id', array('method' => 'a.method'));

//        $collection->getSelect()->joinLeft(array('spt' => 'sales_payment_transaction'),
//            'spt.order_id=main_table.entity_id', array('spt.txn_type', 'spt.txn_id'));
        $clone = clone $collection;
        $fullSize = $clone->getSize();

        /*DO: filter data with data Table*/
        //Set page
        $start = $this->getDataDbTable('start');
        $length = $this->getDataDbTable('length');
        $page = $start / $length + 1;
        $collection->setPageSize($length)
            ->setCurPage($page);

        //Sort Collection

        $a = $this->getDataDbTable('order');
        $collection = $this->sortCollection($collection, $a[0], $this->getDataDbTable('columns'));

        //Filter by column
        $collection = $this->filterByColumn($collection, $this->getDataDbTable('columns'));


        $dataSalesHistory = array();
        $dataSalesHistory['draw'] = $this->getDataDbTable('draw');
        $dataSalesHistory['recordsTotal'] = $fullSize;
        $dataSalesHistory['recordsFiltered'] = $collection->getSize();
        $dataSalesHistory['data'] = array();

        foreach ($collection as $o) {
            $dataSalesHistory['data'][] = array(
                'increment_id' => $o->getData('increment_id'),
                'created_at' => Mage::getSingleton('core/date')->date(null, $o->getData('created_at')),
                'status' => $o->getData('status'),
                'shipping_method' => Mage::helper('xreport')->getCurrentShippingTitle($o->getData('shipping_method')),
                'shipping_code' => $o->getData('shipping_method'),
                'grand_total' => Mage::helper('xreport')->_formatPrice($o->getData('grand_total')),
                'shipping_amount' => Mage::helper('xreport')->_formatPrice($o->getData('shipping_amount')),
                'method' => Mage::helper('xreport')->getConfigDataPaymentMethod($o->getData('method'), 'title'),
                'method_code' => $o->getData('method'),
                'base_subtotal' => Mage::helper('xreport')->_formatPrice($o->getData('base_subtotal')),
                'items' => Mage::helper('xreport')->getAllItemsInOrder($o->getData('entity_id')),
                'base_tax_amount' => Mage::helper('xreport')->_formatPrice($o->getData('base_tax_amount')),
                'base_subtotal_refunded' => Mage::helper('xreport')->_formatPrice($o->getData('base_subtotal_refunded')),
                'discount_amount' => Mage::helper('xreport')->_formatPrice($o->getData('discount_amount')),
                'shipping_tax_amount' => Mage::helper('xreport')->_formatPrice($o->getData('shipping_tax_amount')),
                'shipping_tax_refunded' => Mage::helper('xreport')->_formatPrice($o->getData('shipping_tax_refunded')),
                'shipping_refunded' => Mage::helper('xreport')->_formatPrice($o->getData('shipping_refunded')),
                'total_qty_ordered' => $o->getData('total_qty_ordered'),
                'total_refunded' => Mage::helper('xreport')->_formatPrice($o->getData('total_refunded')),
                'order_currency_code' => $o->getData('base_currency_code'),
                'customer_email' => $o->getData('customer_email'),
                'store_name' => $o->getData('store_name'),
                'sku' => ''
            );
        }
        return $dataSalesHistory;
    }

}
