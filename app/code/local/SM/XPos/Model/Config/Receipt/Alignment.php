<?php
/**
 * Created by PhpStorm.
 * User: Huy
 * Date: 6/30/2015
 * Time: 11:52 AM
 */

class SM_XPos_Model_Config_Receipt_Alignment {
    public function toOptionArray()
    {
        return array(
            array('value'=>'left', 'label'=>Mage::helper('adminhtml')->__('Left')),
            array('value'=>'center', 'label'=>Mage::helper('adminhtml')->__('Center')),
            array('value'=>'right', 'label'=>Mage::helper('adminhtml')->__('Right')),
        );
    }
}