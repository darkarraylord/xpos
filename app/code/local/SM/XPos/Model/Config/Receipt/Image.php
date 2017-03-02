<?php
/**
 * Created by PhpStorm.
 * User: Huy
 * Date: 7/1/2015
 * Time: 9:03 AM
 */

class SM_XPos_Model_Config_Receipt_Image extends Mage_Adminhtml_Model_System_Config_Backend_Image
{

    protected function _getAllowedExtensions()
    {
        return array(
            "jpg",
            'png',
            'gif',
        );
    }

}
