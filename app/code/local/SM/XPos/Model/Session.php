<?php

/**
 * Created by mr.vjcspy@gmail.com/khoild@smartosc.com.
 * Date: 1/14/16
 * Time: 4:58 PM
 */
class SM_XPos_Model_Session extends Mage_Core_Model_Session_Abstract {
    /**
     * Session constructor.
     */
    public function __construct() {
        $this->init('xpos');
    }

    /**
     * @param $key
     * @param $data
     * @return Varien_Object
     */
    public function setCache($key, $data) {
        if (is_array($key))
            $key = $this->getKey($key);
        return $this->setData($key, $data);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getCache($key) {
        if (is_array($key))
            $key = $this->getKey($key);
        return $this->getData($key);
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasCache($key) {
        if (is_array($key))
            $key = $this->getKey($key);
        if (is_null($this->getCache($key)))
            return false;
        else
            return true;
    }

    /**
     * @param array $key
     * @return string
     */
    public function getKey(array $key) {
        if(is_object($key))
            throw new Exception('key can\'t not an object');
        if (is_array($key))
            return json_encode($key);
        else
            return $key;
    }
}
