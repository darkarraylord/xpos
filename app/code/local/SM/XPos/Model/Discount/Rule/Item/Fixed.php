<?php

/**
 * Created by PhpStorm.
 * User: SMART
 * Date: 12/23/2015
 * Time: 7:43 PM
 */
class SM_XPos_Model_Discount_Rule_Item_Fixed extends Mage_SalesRule_Model_Rule implements SM_XPos_Model_Discount_Rule_Interface
{

    protected $_condition;

    /**
     *
     */
    public function dummyData()
{
    $this->addData(array(
        'rule_id' => 'XPos_Item',
        'name' => 'XPos Discount Item',
        'description' => 'XPos Discount Item',
        'from_date' => null,
        'to_date' => null,
        'uses_per_customer' => '0',
        'is_active' => '1',
        'stop_rules_processing' => '0',
        'is_advanced' => '1',
        'product_ids' => null,
        'sort_order' => '0',
        'simple_action' => self::BY_FIXED_ACTION,
        'discount_amount' => 0,
        'discount_qty' => null,
        'discount_step' => '0',
        'simple_free_shipping' => '0',
        'apply_to_shipping' => '0',
        'times_used' => '1',
        'is_rss' => '1',
        'coupon_type' => '1',
        'use_auto_generation' => '0',
        'uses_per_coupon' => null,
        'code' => 'XPos_Item',
        'customer_group_ids' => array(),
        'website_ids' => array(),
        'coupon_code' => null
    ));

}

    public function setItemData($data)
{
    $condition = $this->_getCondition();
    if (isset($data['product_id'])) {
        $condition->setXCondtion($condition::TYPE_PRODUCT_ID, $data['product_id']);
        $id = $data['product_id'];
    } else {
        throw new InvalidArgumentException();
    }
    $this->_addCondition($condition);
    $this->setRuleId($this->getRuleId() + '_' + $id);
}

    protected function _addCondition($condition)
{
    $this->getActions()->addCondition($condition);
}

    protected function _getCondition()
{
    if (!isset($this->_condition)) {
        $this->_condition = Mage::getModel('xpos/discount_rule_item_condition');
    }
    return $this->_condition;
}

    /*
     * Need mocks some method to pass Mage_SalesRule_Model_Validator::_canProcessRule
     * - hasIsValidForAddress
     * */
    /**
     * Check cached validation result for specific address
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  bool
     */
//   public function hasIsValidForAddress($address)
//   {
//      return true;
//   }


}