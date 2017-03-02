<?php
/**
 * Created by Atom.
 * User: Huy
 * Date: 7/22/2015
 */

class SM_XPos_Model_Config_Receipt_DateFormat {
    public function toOptionArray()
    {
        return array(
            array('value'=>'m/d/Y', 'label'=>Mage::helper('adminhtml')->__('m/d/Y ('. date('m/d/Y') .')')),
            array('value'=>'d/m/Y', 'label'=>Mage::helper('adminhtml')->__('d/m/Y ('. date('d/m/Y') .')')),
            array('value'=>'Y/m/d', 'label'=>Mage::helper('adminhtml')->__('Y/m/d ('. date('Y/m/d') .')')),
            array('value'=>'Y/d/m', 'label'=>Mage::helper('adminhtml')->__('Y/d/m ('. date('Y/d/m') .')')),
            array('value'=>'M d Y', 'label'=>Mage::helper('adminhtml')->__('M d Y ('. date('M d Y') .')')),
            array('value'=>'M D Y', 'label'=>Mage::helper('adminhtml')->__('M D Y ('. date('M D Y') .')')),
            array('value'=>'m D Y', 'label'=>Mage::helper('adminhtml')->__('m D Y ('. date('m D Y') .')')),
        );
    }
}
