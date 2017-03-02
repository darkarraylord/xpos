<?php

/**
 * Created by PhpStorm.
 * User: vjcspy
 * Date: 7/13/15
 * Time: 4:50 PM
 */
class SM_XPos_Model_Config_SourceStore /*extends Mage_Core_Model_Config*/
{
    public function toOptionArray()
    {
        $result = null;
        $result= Mage::getResourceModel('core/store_collection')
                ->load()->toOptionArray();
        return $result;
    }
}
