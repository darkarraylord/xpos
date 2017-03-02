<?php
/**
 * Created by PhpStorm.
 * User: Huy
 * Date: 6/30/2015
 * Time: 4:33 PM
 */

class SM_XPos_Model_Config_Receipt_Fields {
    public function toOptionArray()
    {
        return array(
            array('value'=>'tax', 'label'=>Mage::helper('adminhtml')->__('Tax')),
            array('value'=>'subtotal', 'label'=>Mage::helper('adminhtml')->__('Subtotal'))
        );
    }
}