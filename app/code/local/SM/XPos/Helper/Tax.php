<?php

/**
 * Created by PhpStorm.
 * User: hungnt
 * Date: 1/16/16
 * Time: 11:16 AM
 */
class SM_XPos_Helper_Tax extends Mage_Tax_Helper_Data {
    public function getPriceDisplayType($store = null) {
        if (SM_XPos_Model_Tax_Calculation::$_getFromXpos)
            return 2;
        else
            return parent::getPriceDisplayType($store);
    }
}
