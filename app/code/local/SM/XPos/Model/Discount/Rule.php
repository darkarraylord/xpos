<?php
/**
 * Created by PhpStorm.
 * User: sonbv
 * Date: 17/06/2015
 * Time: 15:21
 */

class SM_XPos_Model_Discount_Rule
{

    public static function getRule($type)
    {
        /**@var $rule SM_XPos_Model_Discount_Rule_Interface */
        $rule = Mage::getModel('xpos/discount_rule_' . $type);
        $rule->dummyData();

        return $rule;
    }
}