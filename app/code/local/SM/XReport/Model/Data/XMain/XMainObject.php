<?php

/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 10/26/15
 * Time: 12:09 PM
 */
class SM_XReport_Model_Data_XMain_XMainObject extends Varien_Object {
    private $_params = array();

    public function setParam($name, $value) {
        $this->_params[$name] = $value;
    }

    public function setParams(array $params) {
        $this->_params = $params;
    }

    public function getParam($name) {
        if (isset($this->_params[$name])) {
            return $this->_params[$name];
        }

        return null;
    }

    public function getParams() {
        return $this->_params;
    }
}
