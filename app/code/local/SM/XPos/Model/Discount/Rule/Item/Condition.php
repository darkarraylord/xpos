<?php

/**
 * Created by PhpStorm.
 * User: SMART
 * Date: 12/24/2015
 * Time: 2:03 PM
 */
class SM_XPos_Model_Discount_Rule_Item_Condition extends Mage_Rule_Model_Condition_Abstract
{

    const TYPE_ITEM_ID = 'item_id';
    const TYPE_PRODUCT_ID = 'product_id';

    protected $_condition_type;

    protected $_condition_value;

    public function validate(Varien_Object $object)
    {
        if (!$object instanceof Mage_Sales_Model_Quote_item) {
            return false;
        }
        if (!isset($this->_condition_value) || !isset($this->_condition_type)) {
            return false;
        }
        if ($this->_condition_type === self::TYPE_ITEM_ID) {
            return $this->_condition_value == $object->getId();
        } elseif ($this->_condition_type === self::TYPE_PRODUCT_ID) {
            return $this->_condition_value == $object->getProductId();
        }
        return false;
    }

    public function setXCondtion($type, $value)
    {
        $this->_condition_type  = $type;
        $this->_condition_value = $value;
    }

}