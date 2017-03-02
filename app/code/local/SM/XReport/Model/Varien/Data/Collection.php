<?php

/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 11/17/15
 * Time: 3:31 PM
 */
class SM_XReport_Model_Varien_Data_Collection extends Varien_Data_Collection {
    protected $arrayData = array();

    public function getData($query) {
        $readConnection = $this->getReadConnection();
        /**
         * Execute the query and store the results in $results
         */
        $results = $readConnection->fetchAll($query);

        /**
         * Print out the results
         */

        foreach ($results as $result) {
            if (!$this->filter($result))
                continue;
            $item = $this->resetItem();
            $item->addData($result);
            $this->arrayData[] = $item;
        }
        return $this->arrayData;
    }

    public function getSize() {
        return count($this->arrayData);
    }

    private function resetItem() {
        return new Varien_Object();
    }

    private function getReadConnection() {
        /**
         * Get the resource model
         */
        $resource = Mage::getSingleton('core/resource');

        /**
         * Retrieve the read connection
         */
        $readConnection = $resource->getConnection('core_read');
        return $readConnection;
    }

    private $arrayFilter;

    public function addFilterColumn($columnName, Array $condition) {
        if (is_null($this->arrayFilter))
            $this->arrayFilter = array();
        $this->arrayFilter[$columnName] = $condition;
    }

    private function filter($result) {
        if (is_null($this->arrayFilter))
            return true;
        $match = true;
        foreach ($this->arrayFilter as $columnName => $condition) {
            if (!isset($result[$columnName]))
                return true;
            else {
                switch ($condition['method']) {
                    case '^':
                        if (!(strpos($result[$columnName], $condition['value']) === 0))
                            $match = false;
                        break;
                    case '>':
                        if (intval($result[$columnName]) < $condition['value'])
                            $match = false;
                        break;
                    case '<':
                        if (intval($result[$columnName]) > $condition['value'])
                            $match = false;
                        break;
                    case '=':
                        if ($result[$columnName] != $condition['value'])
                            $match = false;
                        break;
                    case 'like':
                        if (strpos($result[$columnName], $condition['value']) === false)
                            $match = false;
                        break;
                }
            }
        }
        return $match;
    }
}
