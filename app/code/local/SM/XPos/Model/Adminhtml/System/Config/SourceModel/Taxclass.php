<?php

/**
 * Created by mr.vjcspy@gmail.com/khoild@smartosc.com.
 * Date: 12/30/15
 * Time: 2:58 PM
 */
class SM_XPos_Model_Adminhtml_System_Config_SourceModel_Taxclass {
    protected $_options;

    public function toOptionArray() {
        $this->_options = array(
            array(
                'label' => '-- Please Select --',
                'value' => null,
            ), array(
                'label' => 'None',
                'value' => 0,
            )
        );
        $collection = Mage::getModel('tax/class')->getCollection()->addFieldToFilter('class_type', array('eq' => 'PRODUCT'));
        foreach ($collection as $item) {
            $this->_options[] = array(
                'label' => $item->getClassName(),
                'value' => $item->getClassId(),
            );
        }

        return $this->_options;
    }
}
