<?php

    /**
     * Created by PhpStorm.
     * User: vjcspy
     * Date: 9/7/2015
     * Time: 16:58
     */
    class SM_XPos_Block_Adminhtml_Index_Shipping_Method_Form extends Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form {
        public function __construct() {
            parent::__construct();
            $this->setTemplate('sm/xpos/index/shipment/shipment.phtml');
        }
    }
