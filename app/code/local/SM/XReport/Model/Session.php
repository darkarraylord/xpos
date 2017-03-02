<?php

/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 11/2/15
 * Time: 2:14 PM
 */
class SM_XReport_Model_Session extends Mage_Core_Model_Session_Abstract {

    public function __construct() {
        $namespace = 'xreport';
        $namespace .= '_' . (Mage::app()->getStore()->getWebsite()->getCode());

        $this->init($namespace);
        Mage::dispatchEvent('xreport_session_init', array('xreport_session' => $this));
    }
}
