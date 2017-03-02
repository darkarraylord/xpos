<?php

/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 9/23/15
 * Time: 9:55 AM
 */
class SM_XPos_Model_Adminhtml_System_Config_SourceModel_TypeLoad {
    public function toOptionArray() {
        $result = array();
        $result[] = array('value' => 0, 'label' => 'Optimized');
        $result[] = array('value' => 1, 'label' => 'Normal');

        return $result;
    }
}
