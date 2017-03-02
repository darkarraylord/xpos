<?php

/**
 * Created by mr.vjcspy@gmail.com/khoild@smartosc.com.
 * Date: 1/18/16
 * Time: 11:11 AM
 */
class SM_XPos_Model_Cache extends Mage_Core_Model_Abstract {

    /**
     * @var Zend_Cache_Core
     */
    protected $_cache;

    protected $_cacheData = array();

    /**
     * XPosCache constructor.
     */
    public function __construct() {
        $this->_cache = Mage::app()->getCache();
        parent::__construct();
    }

    /**
     * TODO: flush all cache XPOS
     */
    public function flushXPOSCache() {
        Mage::dispatchEvent('adminhtml_cache_flush_xpos');
        Mage::app()->getCache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('xpos_cache'));
        $this->_getSession()->addSuccess(Mage::helper('adminhtml')->__("The cache X-POS has been flushed."));
    }

    /**
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession() {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * @param $data
     * @param $key
     * @param int $time
     * @return bool
     * @throws Exception
     */
    public function setXPosCache($data, $key, $time = 9999999) {
        if (is_null($key))
            throw new Exception('Can\'t Save cache');
        if (is_array($key))
            $key = json_encode($key);
        if(is_array($data))
            $data = json_encode($data);
        return $this->_cache->save($data, $key, array("xpos_cache"), $time);
    }

    /**
     * @param $key
     * @return false|mixed
     * @throws Exception
     */
    public function getXPosCache($key) {
        if (is_null($key))
            throw new Exception('Can\'t get cache');
        if (is_array($key))
            $key = json_encode($key);
        if (!isset($this->_cacheData[$key]))
            $this->_cacheData[$key] = $this->_cache->load($key);

        return json_decode($this->_cacheData[$key],true);
    }

}
