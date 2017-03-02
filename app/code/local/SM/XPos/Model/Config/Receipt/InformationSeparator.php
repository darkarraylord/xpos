<?php
/**
 * Created by PhpStorm.
 * User: Huy
 * Date: 7/6/2015
 * Time: 10:40 AM
 */

class SM_XPos_Model_Config_Receipt_InformationSeparator {
    public function toOptionArray()
    {
        return array(
            array('value'=>'none', 'label'=>Mage::helper('adminhtml')->__('None')),
            array('value'=>'line', 'label'=>Mage::helper('adminhtml')->__('Line')),
            array('value'=>'dash', 'label'=>Mage::helper('adminhtml')->__('Dashed'))
        );
    }
}