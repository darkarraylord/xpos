<?php

/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 11/2/15
 * Time: 11:09 AM
 */
class SM_XReport_Block_Adminhtml_Sales_Detail_Grid extends SM_XReport_Block_DataTable_DataTableBlockAbstract {


    public function getDataCollection() {
        $collection = $this->_collection->addAttributeToSelect('*');
        $dataTable = $this->getDataDbTable('search');

        $table = array(
            'customer_entity' => Mage::helper('xreport/sql_data')->getTable('customer_entity'),
            'customer_group' => Mage::helper('xreport/sql_data')->getTable('customer_group'),
        );

        if ($dataTable['value'] == '') {
            $collection->getSelect()
                ->columns('COUNT(*) AS quantity')
                ->columns('SUM(grand_total) as grand_total')
                ->columns('SUM(main_table.shipping_amount) as shipping_amount')
                ->columns('SUM(base_subtotal) as base_subtotal')
                ->columns('SUM(base_tax_amount) as base_tax_amount')
                ->columns('SUM(base_subtotal_refunded) as base_subtotal_refunded')
                ->columns('SUM(discount_amount) as discount_amount')
                ->columns('SUM(shipping_tax_amount) as shipping_tax_amount')
                ->columns('SUM(shipping_tax_refunded) as shipping_tax_refunded')
                ->columns('SUM(main_table.shipping_refunded) as shipping_refunded')
                ->columns('SUM(total_qty_ordered) as total_qty_ordered')
                ->columns('SUM(total_refunded) as total_refunded')
                ->group('method');
        } else {
            switch ($dataTable['value']) {
                case 'shipping_method':
                    $collection->getSelect()
                        ->columns('COUNT(*) AS quantity')
                        ->columns('SUM(grand_total) as grand_total')
                        ->columns('SUM(main_table.shipping_amount) as shipping_amount')
                        ->columns('SUM(base_subtotal) as base_subtotal')
                        ->columns('SUM(base_tax_amount) as base_tax_amount')
                        ->columns('SUM(base_subtotal_refunded) as base_subtotal_refunded')
                        ->columns('SUM(discount_amount) as discount_amount')
                        ->columns('SUM(shipping_tax_amount) as shipping_tax_amount')
                        ->columns('SUM(shipping_tax_refunded) as shipping_tax_refunded')
                        ->columns('SUM(main_table.shipping_refunded) as shipping_refunded')
                        ->columns('SUM(total_qty_ordered) as total_qty_ordered')
                        ->columns('SUM(total_refunded) as total_refunded')
                        ->group('shipping_method');
                    break;
                case 'status':
                    $collection->getSelect()
                        ->columns('COUNT(*) AS quantity')
                        ->columns('SUM(grand_total) as grand_total')
                        ->columns('SUM(main_table.shipping_amount) as shipping_amount')
                        ->columns('SUM(base_subtotal) as base_subtotal')
                        ->columns('SUM(base_tax_amount) as base_tax_amount')
                        ->columns('SUM(base_subtotal_refunded) as base_subtotal_refunded')
                        ->columns('SUM(discount_amount) as discount_amount')
                        ->columns('SUM(shipping_tax_amount) as shipping_tax_amount')
                        ->columns('SUM(shipping_tax_refunded) as shipping_tax_refunded')
                        ->columns('SUM(main_table.shipping_refunded) as shipping_refunded')
                        ->columns('SUM(total_qty_ordered) as total_qty_ordered')
                        ->columns('SUM(total_refunded) as total_refunded')
                        ->group('status');
                    break;
                case 'sku':
//                    $collection = Mage::getModel('sales/order_item')->getCollection();
                    $collection->join(array('order' => 'sales/order'), 'order.entity_id=main_table.order_id', array('total_refunded', 'total_qty_ordered', 'shipping_tax_refunded', 'shipping_tax_amount', 'base_subtotal_refunded', 'grand_total', 'shipping_amount', 'base_subtotal'), null, 'left');
                    $collection->getSelect()
                        ->columns('COUNT(*) AS quantity')
                        ->columns('SUM(order.grand_total) as grand_total')
                        ->columns('SUM(shipping_amount) as shipping_amount')
                        ->columns('SUM(base_subtotal) as base_subtotal')
                        ->columns('SUM(main_table.base_tax_amount) as base_tax_amount')
                        ->columns('SUM(base_subtotal_refunded) as base_subtotal_refunded')
                        ->columns('SUM(main_table.discount_amount) as discount_amount')
                        ->columns('SUM(shipping_tax_amount) as shipping_tax_amount')
                        ->columns('SUM(shipping_tax_refunded) as shipping_tax_refunded')
                        ->columns('SUM(shipping_refunded) as shipping_refunded')
                        ->columns('SUM(total_qty_ordered) as total_qty_ordered')
                        ->columns('SUM(total_refunded) as total_refunded')->group('sku');
                    break;
                case 'customer_group_code':
                    $collection->getSelect()->joinLeft(array('a' => $table['customer_entity']), 'a.email=main_table.customer_email', array('a.customer_group_id' => 'a.group_id'));
                    $collection->getSelect()->joinLeft(array('b' => $table['customer_group']), 'b.customer_group_id=a.group_id', array('customer_group_code' => 'b.customer_group_code'));
                    $collection->getSelect()
                        ->columns('COUNT(*) AS quantity')
                        ->columns('SUM(grand_total) as grand_total')
                        ->columns('SUM(main_table.shipping_amount) as shipping_amount')
                        ->columns('SUM(base_subtotal) as base_subtotal')
                        ->columns('SUM(base_tax_amount) as base_tax_amount')
                        ->columns('SUM(base_subtotal_refunded) as base_subtotal_refunded')
                        ->columns('SUM(discount_amount) as discount_amount')
                        ->columns('SUM(shipping_tax_amount) as shipping_tax_amount')
                        ->columns('SUM(shipping_tax_refunded) as shipping_tax_refunded')
                        ->columns('SUM(main_table.shipping_refunded) as shipping_refunded')
                        ->columns('SUM(total_qty_ordered) as total_qty_ordered')
                        ->columns('SUM(total_refunded) as total_refunded')
                        ->group('customer_group_code');
                    break;
                case 'customer_email':
                    $collection->getSelect()
                        ->columns('COUNT(*) AS quantity')
                        ->columns('SUM(grand_total) as grand_total')
                        ->columns('SUM(main_table.shipping_amount) as shipping_amount')
                        ->columns('SUM(base_subtotal) as base_subtotal')
                        ->columns('SUM(base_tax_amount) as base_tax_amount')
                        ->columns('SUM(base_subtotal_refunded) as base_subtotal_refunded')
                        ->columns('SUM(discount_amount) as discount_amount')
                        ->columns('SUM(shipping_tax_amount) as shipping_tax_amount')
                        ->columns('SUM(shipping_tax_refunded) as shipping_tax_refunded')
                        ->columns('SUM(main_table.shipping_refunded) as shipping_refunded')
                        ->columns('SUM(total_qty_ordered) as total_qty_ordered')
                        ->columns('SUM(total_refunded) as total_refunded')
                        ->group('customer_email');
                    break;
                case 'product_type':
//                    $collection = Mage::getModel('sales/order_item')->getCollection();
                    $collection->join(array('order' => 'sales/order'), 'order.entity_id=main_table.order_id', array('total_refunded', 'total_qty_ordered', 'shipping_tax_refunded', 'shipping_tax_amount', 'base_subtotal_refunded', 'grand_total', 'shipping_amount', 'base_subtotal'), null, 'left');
                    $collection->getSelect()
                        ->columns('COUNT(*) AS quantity')
                        ->columns('SUM(order.grand_total) as grand_total')
                        ->columns('SUM(shipping_amount) as shipping_amount')
                        ->columns('SUM(base_subtotal) as base_subtotal')
                        ->columns('SUM(main_table.base_tax_amount) as base_tax_amount')
                        ->columns('SUM(base_subtotal_refunded) as base_subtotal_refunded')
                        ->columns('SUM(main_table.discount_amount) as discount_amount')
                        ->columns('SUM(shipping_tax_amount) as shipping_tax_amount')
                        ->columns('SUM(shipping_tax_refunded) as shipping_tax_refunded')
                        ->columns('SUM(shipping_refunded) as shipping_refunded')
                        ->columns('SUM(total_qty_ordered) as total_qty_ordered')
                        ->columns('SUM(total_refunded) as total_refunded')
                        ->group('product_type');
                    break;
                case 'order_currency_code':
                    $collection->getSelect()
                        ->columns('COUNT(*) AS quantity')
                        ->columns('SUM(grand_total) as grand_total')
                        ->columns('SUM(main_table.shipping_amount) as shipping_amount')
                        ->columns('SUM(base_subtotal) as base_subtotal')
                        ->columns('SUM(base_tax_amount) as base_tax_amount')
                        ->columns('SUM(base_subtotal_refunded) as base_subtotal_refunded')
                        ->columns('SUM(discount_amount) as discount_amount')
                        ->columns('SUM(shipping_tax_amount) as shipping_tax_amount')
                        ->columns('SUM(shipping_tax_refunded) as shipping_tax_refunded')
                        ->columns('SUM(main_table.shipping_refunded) as shipping_refunded')
                        ->columns('SUM(total_qty_ordered) as total_qty_ordered')
                        ->columns('SUM(total_refunded) as total_refunded')
                        ->group('order_currency_code');
                    break;
                case 'day_of_week':
                    $collection->getSelect()
                        ->columns('COUNT(*) AS quantity')
                        ->columns('SUM(grand_total) as grand_total')
                        ->columns('SUM(main_table.shipping_amount) as shipping_amount')
                        ->columns('SUM(base_subtotal) as base_subtotal')
                        ->columns('SUM(base_tax_amount) as base_tax_amount')
                        ->columns('SUM(base_subtotal_refunded) as base_subtotal_refunded')
                        ->columns('SUM(discount_amount) as discount_amount')
                        ->columns('SUM(shipping_tax_amount) as shipping_tax_amount')
                        ->columns('SUM(shipping_tax_refunded) as shipping_tax_refunded')
                        ->columns('SUM(main_table.shipping_refunded) as shipping_refunded')
                        ->columns('SUM(total_qty_ordered) as total_qty_ordered')
                        ->columns('SUM(total_refunded) as total_refunded')
                        ->group('day_of_week');
                    break;
                case 'hour':
                    $collection->getSelect()
                        ->columns('COUNT(*) AS quantity')
                        ->columns('SUM(grand_total) as grand_total')
                        ->columns('SUM(main_table.shipping_amount) as shipping_amount')
                        ->columns('SUM(base_subtotal) as base_subtotal')
                        ->columns('SUM(base_tax_amount) as base_tax_amount')
                        ->columns('SUM(base_subtotal_refunded) as base_subtotal_refunded')
                        ->columns('SUM(discount_amount) as discount_amount')
                        ->columns('SUM(shipping_tax_amount) as shipping_tax_amount')
                        ->columns('SUM(shipping_tax_refunded) as shipping_tax_refunded')
                        ->columns('SUM(main_table.shipping_refunded) as shipping_refunded')
                        ->columns('SUM(total_qty_ordered) as total_qty_ordered')
                        ->columns('SUM(total_refunded) as total_refunded')
                        ->group('hour');
                    break;
                default:
                    $collection->getSelect()
                        ->columns('COUNT(*) AS quantity')
                        ->columns('SUM(grand_total) as grand_total')
                        ->columns('SUM(main_table.shipping_amount) as shipping_amount')
                        ->columns('SUM(base_subtotal) as base_subtotal')
                        ->columns('SUM(main_table.base_tax_amount) as base_tax_amount')
                        ->columns('SUM(base_subtotal_refunded) as base_subtotal_refunded')
                        ->columns('SUM(discount_amount) as discount_amount')
                        ->columns('SUM(shipping_tax_amount) as shipping_tax_amount')
                        ->columns('SUM(shipping_tax_refunded) as shipping_tax_refunded')
                        ->columns('SUM(main_table.shipping_refunded) as shipping_refunded')
                        ->columns('SUM(total_qty_ordered) as total_qty_ordered')
                        ->columns('SUM(total_refunded) as total_refunded')
                        ->group('method');
                    break;
            }
        }
        $clone = clone $collection;
        $fullSize = $clone->count();

        /*DO: filter data with data Table*/
        //Filter by column
        $collection = $this->filterByColumn($collection, $this->getDataDbTable('columns'));
        //Sort Collection
        $dataTable = $this->getDataDbTable('order');
        $collection = $this->sortCollection($collection, $dataTable[0], $this->getDataDbTable('columns'));

        $clone = clone $collection;
        $sizeFiltered = $clone->count();

        //Set page
        $start = $this->getDataDbTable('start');
        $length = $this->getDataDbTable('length');

        $dataSalesHistory = array();
        $dataSalesHistory['draw'] = $this->getDataDbTable('draw');
        $dataSalesHistory['recordsTotal'] = $fullSize;
        $dataSalesHistory['recordsFiltered'] = $sizeFiltered;
        $dataSalesHistory['data'] = array();


        $c = 0;
        foreach ($collection as $o) {

            /*FIXME: Hard code*/
            $c += 1;
            if ($c < $start)
                continue;
            else if ($c >= ($start + $length))
                break;

            $dataTableSearch = $this->getDataDbTable('search');
            if ($dataTableSearch['value'] == 'sku') {
                $sku = $o->getData('sku');
                $dataSalesHistory['data'][] = array(
                    'method' => '',
                    'status' => '',
                    'shipping_method' => '',
                    'sku' => $sku,
                    'customer_group_code' => '',
                    'customer_email' => '',
                    'product_type' => '',
                    'order_currency_code' => '',
                    'country' => '',
                    'day_of_week' => '',
                    'hour' => '',

                    'created_at' => $o->getData('created_at'),

                    'quantity' => $o->getData('quantity'),
                    'grand_total' => Mage::helper('xreport')->_formatPrice($o->getData('grand_total')),
                    'shipping_amount' => Mage::helper('xreport')->_formatPrice($o->getData('shipping_amount')),
                    //                'method_code' => $o->getData('method'),
                    'base_subtotal' => Mage::helper('xreport')->_formatPrice($o->getData('base_subtotal')),
                    'base_tax_amount' => Mage::helper('xreport')->_formatPrice($o->getData('base_tax_amount')),
                    'base_subtotal_refunded' => Mage::helper('xreport')->_formatPrice($o->getData('base_subtotal_refunded')),
                    'discount_amount' => Mage::helper('xreport')->_formatPrice($o->getData('discount_amount')),
                    'shipping_tax_amount' => Mage::helper('xreport')->_formatPrice($o->getData('shipping_tax_amount')),
                    'shipping_tax_refunded' => Mage::helper('xreport')->_formatPrice($o->getData('shipping_tax_refunded')),
                    'shipping_refunded' => Mage::helper('xreport')->_formatPrice($o->getData('shipping_refunded')),
                    'total_qty_ordered' => $o->getData('total_qty_ordered'),
                    'total_refunded' => Mage::helper('xreport')->_formatPrice($o->getData('total_refunded'))
                );
            } else {
                $sku = '';
                $customer_group_code = '';
                $product_type = '';
                $day_of_week = '';
                $hour = '';
                $dataTableSearch = $this->getDataDbTable('search');
                switch ($dataTableSearch['value']) {
                    case 'customer_group_code':
                        $customer_group_code = $o->getData('customer_group_code');
                        break;
                    case 'product_type':
                        $product_type = $o->getData('product_type');
                        break;
                    case 'day_of_week':
                        $day_of_week = $o->getData('day_of_week');
                        break;
                    case 'hour':
                        $hour = $o->getData('hour');
                        break;
                }

                $dataSalesHistory['data'][] = array(
                    'method' => Mage::helper('xreport')->getConfigDataPaymentMethod($o->getData('method'), 'title'),
                    'status' => $o->getData('status'),
                    'shipping_method' => Mage::helper('xreport')->getCurrentShippingTitle($o->getData('shipping_method')),
                    'sku' => $sku,
                    'customer_group_code' => $customer_group_code,
                    'customer_email' => $o->getData('customer_email'),
                    'product_type' => $product_type,
                    'order_currency_code' => $o->getData('base_currency_code'),
                    'country' => '',
                    'day_of_week' => $day_of_week,
                    'hour' => $hour,

                    'created_at' => $o->getData('created_at'),

                    'quantity' => $o->getData('quantity'),
                    'grand_total' => Mage::helper('xreport')->_formatPrice($o->getData('grand_total')),
                    'shipping_amount' => Mage::helper('xreport')->_formatPrice($o->getData('shipping_amount')),
                    //                'method_code' => $o->getData('method'),
                    'base_subtotal' => Mage::helper('xreport')->_formatPrice($o->getData('base_subtotal')),
                    'base_tax_amount' => Mage::helper('xreport')->_formatPrice($o->getData('base_tax_amount')),
                    'base_subtotal_refunded' => Mage::helper('xreport')->_formatPrice($o->getData('base_subtotal_refunded')),
                    'discount_amount' => Mage::helper('xreport')->_formatPrice($o->getData('discount_amount')),
                    'shipping_tax_amount' => Mage::helper('xreport')->_formatPrice($o->getData('shipping_tax_amount')),
                    'shipping_tax_refunded' => Mage::helper('xreport')->_formatPrice($o->getData('shipping_tax_refunded')),
                    'shipping_refunded' => Mage::helper('xreport')->_formatPrice($o->getData('shipping_refunded')),
                    'total_qty_ordered' => $o->getData('total_qty_ordered'),
                    'total_refunded' => Mage::helper('xreport')->_formatPrice($o->getData('total_refunded')),
                );
            }
        }
        return $dataSalesHistory;
    }

    public function filterByColumn($collection, $arrayDataFillter) {
        $exactly = '/^".*"$/';
        $first = '/^\^.*/';

        $grand = '/(^>)=?\d*/';
        $less = '/(^<)=?\d*/';
        $eq = '/(^=)\d*/';

        foreach ($arrayDataFillter as $col) {
            if ($col['search']['value'] != '') {

                $columnName = $col['data'];

                // Loai created_at boi vi da filter o ben ngoai
                if ($columnName == 'created_at')
                    continue;

                $searchValue = $col['search']['value'];

                if (in_array($columnName, array('quantity', 'hour', 'day_of_week', 'grand_total', 'shipping_amount', 'base_subtotal', 'base_tax_amount', 'base_subtotal_refunded', 'discount_amount', 'shipping_tax_amount', 'shipping_tax_refunded', 'total_qty_ordered', 'total_refunded'))) {
                    $dataTableSearch = $this->getDataDbTable('search');
                    if (in_array($dataTableSearch['value'], array('sku', 'product_type')))
                        $collectionType = 'item';
                    else
                        $collectionType = 'order';

                    if (preg_match($grand, $searchValue)) {
                        $searchValue = str_replace('>', '', $searchValue);
                        $typeSearch = '>';
                    } else if (preg_match($less, $searchValue)) {
                        $searchValue = str_replace('<', '', $searchValue);
                        $typeSearch = '<';
                    } else {
                        $searchValue = str_replace('=', '', $searchValue);
                        $typeSearch = '=';
                    }

                    $globalTz = Mage::helper('xreport/sql_data')->getTimeZoneOffset(true);
                    switch ($columnName) {
                        case 'hour':
                            $collection->getSelect()->having("HOUR(CONVERT_TZ(main_table.created_at, '+00:00', '{$globalTz}'))" . $typeSearch . ' ?', $searchValue);
                            break;
                        case 'day_of_week':
                            $collection->getSelect()->having("DAYOFWEEK(CONVERT_TZ(main_table.created_at, '+00:00', '{$globalTz}'))" . $typeSearch . ' ?', $searchValue);
                            break;
                        case 'quantity':
                            $collection->getSelect()->having('COUNT(*) ' . $typeSearch . ' ?', $searchValue);
                            break;
                        case 'grand_total':
                            if ($collectionType == 'order')
                                $collection->getSelect()->having('SUM(grand_total) ' . $typeSearch . ' ?', $searchValue);
                            else
                                $collection->getSelect()->having('SUM(order.grand_total) ' . $typeSearch . ' ?', $searchValue);
                            break;
                        case 'shipping_amount':
                            if ($collectionType == 'order')
                                $collection->getSelect()->having('SUM(main_table.shipping_amount) ' . $typeSearch . ' ?', $searchValue);
                            else
                                $collection->getSelect()->having('SUM(shipping_amount) ' . $typeSearch . ' ?', $searchValue);
                            break;
                        case 'base_subtotal':
                            $collection->getSelect()->having('SUM(base_subtotal) ' . $typeSearch . ' ?', $searchValue);
                            break;
                        case 'base_tax_amount':
                            if ($collectionType == 'order')
                                $collection->getSelect()->having('SUM(base_tax_amount) ' . $typeSearch . ' ?', $searchValue);
                            else
                                $collection->getSelect()->having('SUM(main_table.base_tax_amount) ' . $typeSearch . ' ?', $searchValue);
                            break;
                        case 'base_subtotal_refunded':
                            $collection->getSelect()->having('SUM(base_subtotal_refunded) ' . $typeSearch . ' ?', $searchValue);
                            break;
                        case 'discount_amount':
                            if ($collectionType == 'order')
                                $collection->getSelect()->having('SUM(discount_amount) ' . $typeSearch . ' ?', $searchValue);
                            else
                                $collection->getSelect()->having('SUM(main_table.discount_amount) ' . $typeSearch . ' ?', $searchValue);
                            break;
                        case 'shipping_tax_amount':
                            $collection->getSelect()->having('SUM(shipping_tax_amount) ' . $typeSearch . ' ?', $searchValue);
                            break;
                        case 'shipping_tax_refunded':
                            $collection->getSelect()->having('SUM(shipping_tax_refunded) ' . $typeSearch . ' ?', $searchValue);
                            break;
                        case 'total_qty_ordered':
                            $collection->getSelect()->having('SUM(total_qty_ordered) ' . $typeSearch . ' ?', $searchValue);
                            break;
                        case 'total_refunded':
                            $collection->getSelect()->having('SUM(total_refunded) ' . $typeSearch . ' ?', $searchValue);
                            break;

                    }
                } else {
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
        }
        return $collection;
    }

    public function initCollection($customStart = null, $customEnd = null) {


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

//            $customStart = Mage::app()->getLocale()->storeDate(null, $customStart, null, 'YYYY-MM-dd');
//            $customEnd = Mage::app()->getLocale()->storeDate(null, $customEnd, null, 'YYYY-MM-dd');

            $customEnd = Mage::app()->getLocale()->date($customEnd, null, null, false);
            $customStart = Mage::app()->getLocale()->date($customStart, null, null, false);

            $customEnd->setHour(23);
            $customEnd->setMinute(59);
            $customEnd->setSecond(59);

            $customStart->setHour(0);
            $customStart->setMinute(0);
            $customStart->setSecond(0);
        }
        $dataTableSearch = $this->getDataDbTable('search');
        if (in_array($dataTableSearch['value'], array('sku', 'product_type')))
            $collection = Mage::getModel('xreport/resource_order_item_collection')->getOrderItemCollection('custom', $customStart, $customEnd);
        else {
            $collection = $this->getBlockHelper()->getSalesHistoryCollection('custom', $customStart, $customEnd);
            $globalTz = Mage::helper('xreport/sql_data')->getTimeZoneOffset(true);
            $collection->join(array('payment' => 'sales/order_payment'), 'main_table.entity_id=payment.parent_id', array(
                'method' => 'method', 'day_of_week' => "DAYOFWEEK(CONVERT_TZ(main_table.created_at, '+00:00', '{$globalTz}'))",
                'hour' => "HOUR(CONVERT_TZ(main_table.created_at, '+00:00', '{$globalTz}'))",), null, 'left');
        }
        if (Mage::getSingleton('xreport/session')->getData('store_id') != 0)
            $collection->addFieldToFilter('main_table.store_id', Mage::getSingleton('xreport/session')->getData('store_id'));

        $this->setCollection($collection);
    }
}
