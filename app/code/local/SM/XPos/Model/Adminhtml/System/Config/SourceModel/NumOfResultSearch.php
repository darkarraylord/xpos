<?php

    /**
     * Created by PhpStorm.
     * User: vjcspy
     * Date: 8/5/15
     * Time: 2:35 PM
     */
    class SM_XPos_Model_Adminhtml_System_Config_SourceModel_NumOfResultSearch
    {
        public function toOptionArray()
        {
            $result = array();
            $numOfRecordPerRequest = array(50, 75, 100, 125, 150, 175, 200);
            foreach ($numOfRecordPerRequest as $num) {
                $result[] = array('value' => $num, 'label' => $num . ' items');
            }

            return $result;
        }
    }
