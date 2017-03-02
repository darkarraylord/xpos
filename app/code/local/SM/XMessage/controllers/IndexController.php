<?php

/**
 * Created by PhpStorm.
 * User: SMART
 * Date: 10/16/2015
 * Time: 3:10 PM
 */
class SM_XMessage_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout()->renderLayout();
    }
}