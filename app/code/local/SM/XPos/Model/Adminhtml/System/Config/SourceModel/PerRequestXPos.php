<?php

/**
 * Created by PhpStorm.
 * User: vjcspy
 * Date: 7/20/15
 * Time: 4:03 PM
 */
class SM_XPos_Model_Adminhtml_System_Config_SourceModel_PerRequestXPos
{
    public function toOptionArray()
    {
        $result = array();
        $numOfRecordPerRequest = array(50, 100, 150, 200, 250, 300, 350, 400, 450, 500);
        foreach ($numOfRecordPerRequest as $num) {
            $result[] = array('value' => $num, 'label' => $num . ' items');
        }

        return $result;
    }
}
