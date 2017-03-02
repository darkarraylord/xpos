<?php

/**
 * Created by PhpStorm.
 * User: SMART
 * Date: 10/15/2015
 * Time: 5:36 PM
 */
class SM_XMessage_Block_Adminhtml_XMessage extends Mage_Adminhtml_Block_Template
{
    public function __construct() {
        parent::__construct();
        $this->setTemplate('sm/xmessage/index.phtml');
        $this->setFormAction(Mage::getUrl('*/*/sendMessage'));
    }
}