<?php

/**
 * Created by PhpStorm.
 * User: SMART
 * Date: 12/23/2015
 * Time: 5:47 PM
 */
class SM_XPos_Helper_Discount_Item
{
    protected $_discountItems;

    /**
     * @param $itemId
     * @return mixed
     */
    public function getDiscountItem($itemId)
    {
        foreach ($this->getDiscountItems() as $discountItem) {
            if ($itemId == $discountItem['product_id']) {
                return $discountItem['discount'];
            }
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getDiscountItems()
    {
        if (!isset($this->_discountItems)) {
            $this->_discountItems = Mage::getSingleton('xpos/discount_item_collection')->getItems();
        }
        return $this->_discountItems;
    }


}