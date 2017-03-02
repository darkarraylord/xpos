<?php
/**
 * Created by Atom.
 * User: Huy
 * Date: 7/22/2015
 */

class SM_XPos_Model_Config_Receipt_Fonts {
    public function toOptionArray()
    {
        return array(
            array('value'=>'monospace', 'label'=>Mage::helper('adminhtml')->__('monospace')),
            array('value'=>'sans-serif', 'label'=>Mage::helper('adminhtml')->__('sans-serif')),
        );
    }
}
