<?php
require_once(Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'CacheController.php');

/**
 * Created by PhpStorm.
 * User: vjcspy
 * Date: 7/21/15
 * Time: 2:47 PM
 */
class SM_XPos_Adminhtml_CacheController extends Mage_Adminhtml_Controller_Action
{
    public function flushXPosCacheAction()
    {
        Mage::helper('xpos/realTimeProduct')->flushXPOSCache();
        $this->_redirect('*/*');
    }
}
