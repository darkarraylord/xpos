<?php

/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 11/17/15
 * Time: 9:43 AM
 */
class SM_XReport_Block_Adminhtml_Sales_Inventory_Grid extends SM_XReport_Block_DataTable_DataTableBlockAbstract {
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

            $customEnd = Mage::app()->getLocale()->date($customEnd,null,null,false);
            $customStart = Mage::app()->getLocale()->date($customStart,null,null,false);

            $customEnd->setHour(23);
            $customEnd->setMinute(59);
            $customEnd->setSecond(59);

            $customStart->setHour(0);
            $customStart->setMinute(0);
            $customStart->setSecond(0);
        }

        $collection = Mage::getModel('xreport/resource_inventory_collection');
        $estimationDays = 90;

        $collection
            ->initSelect()
            ->addOrderItems()
            ->addProductInfo()
            ->addEstimationThreshold($estimationDays);

        $collection->setDateFilter($customStart, $customEnd);
//            ->setState();

        if (Mage::getSingleton('xreport/session')->getData('store_id') != 0)
            $collection->addFieldToFilter('main_table.store_id', Mage::getSingleton('xreport/session')->getData('store_id'));

        $this->setCollection($collection);
    }

    public function getDataCollection() {
        $collection = $this->_collection->addAttributeToSelect('*');

        /*DO: filter data with data Table*/

        //Sort Collection
//        $collection = $this->sortCollection($collection, $this->getDataDbTable('order')[0], $this->getDataDbTable('columns'));

        //Filter by column
        $this->filterByColumn($collection, $this->getDataDbTable('columns'));


        $dataSalesHistory = array();
        $dataSalesHistory['draw'] = $this->getDataDbTable('draw');
        $dataSalesHistory['data'] = array();

        $query = $collection->getSelect()->__toString();

        $invetoryResource = $this->getInventoryResource();
        $izCollection = $invetoryResource->getData($query);
        $dataSalesHistory['recordsFiltered'] = $dataSalesHistory['recordsTotal'] = $invetoryResource->getSize();

        foreach ($izCollection as $o) {
            $dataSalesHistory['data'][] = array(
                'name' => $o->getData('name'),
                'sku' => $o->getData('sku'),
                'price' => Mage::helper('xreport')->_formatPrice($o->getData('price')),
                'sum_qty' => $o->getData('sum_qty'),
                'sum_total' => Mage::helper('xreport')->_formatPrice($o->getData('sum_total')),
                'sum_invoiced' => Mage::helper('xreport')->_formatPrice($o->getData('sum_invoiced')),
                'sum_refunded' => Mage::helper('xreport')->_formatPrice($o->getData('sum_refunded')),
                'cost' => Mage::helper('xreport')->_formatPrice($o->getData('cost')),
                'item_qty' => $o->getData('item_qty'),
                'discount_amount' => Mage::helper('xreport')->_formatPrice($o->getData('discount_amount')),
                'esitmation_data' => $o->getData('esitmation_data'),
                'created_at' => $o->getData('created_at')
            );
        }
        return $dataSalesHistory;
    }

    private $_inventoryResouceModel;

    private function getInventoryResource() {
        if (is_null($this->_inventoryResouceModel))
            $this->_inventoryResouceModel = Mage::getModel('xreport/resource_inventory_resource');
        return $this->_inventoryResouceModel;
    }

    public function filterByColumn($collection, $arrayDataFillter) {
        $exactly = '/^".*"$/';
        $first = '/^\^.*/';

        $grand = '/(^>)=?\d*/';
        $less = '/(^<)=?\d*/';
        $eq = '/(^=)\d*/';
        $inventoryResource = $this->getInventoryResource();
        foreach ($arrayDataFillter as $col) {
            if ($col['search']['value'] != '') {

                $columnName = $col['data'];

                // Loai created_at boi vi da filter o ben ngoai
                if ($columnName == 'created_at')
                    continue;

                $searchValue = $col['search']['value'];
                if (preg_match($first, $searchValue)) {
                    $searchValue = str_replace('^', '', $searchValue);
                    $inventoryResource->addFilterColumn($columnName, array(
                        'method' => '^',
                        'value' => $searchValue));
                } else if (preg_match($exactly, $searchValue)) {
                    $searchValue = str_replace('"', '', $searchValue);
                    $inventoryResource->addFilterColumn($columnName, array(
                        'method' => '=',
                        'value' => $searchValue));
                } else if (preg_match($grand, $searchValue)) {
                    $searchValue = str_replace('>', '', $searchValue);
                    $searchValue = str_replace('=', '', $searchValue);
                    $inventoryResource->addFilterColumn($columnName, array(
                        'method' => '>',
                        'value' => $searchValue));
                } else if (preg_match($less, $searchValue)) {
                    $searchValue = str_replace('<', '', $searchValue);
                    $searchValue = str_replace('=', '', $searchValue);
                    $inventoryResource->addFilterColumn($columnName, array(
                        'method' => '<',
                        'value' => $searchValue));
                } else if (preg_match($eq, $searchValue)) {
                    $searchValue = str_replace('=', '', $searchValue);
                    $inventoryResource->addFilterColumn($columnName, array(
                        'method' => '=',
                        'value' => $searchValue));
                } else {
                    $inventoryResource->addFilterColumn($columnName, array(
                        'method' => 'like',
                        'value' => $searchValue));
                }
            }
        }
        return $collection;
    }
}
