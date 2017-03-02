<?php
/**
 * Created by PhpStorm.
 * User: sonbv
 * Date: 17/06/2015
 * Time: 14:17
 */

class SM_XPos_Model_Discount_Rule_Fixed extends Mage_SalesRule_Model_Rule
{
/*
 * Example rule
{
   "rule_id":"1",
   "name":"code123",
   "description":null,
   "from_date":"2015-06-12",
   "to_date":null,
   "uses_per_customer":"0",
   "is_active":"1",
   "stop_rules_processing":"0",
   "is_advanced":"1",
   "product_ids":null,
   "sort_order":"0",
   "simple_action":"cart_fixed",
   "discount_amount":"12.0000",
   "discount_qty":null,
   "discount_step":"0",
   "simple_free_shipping":"0",
   "apply_to_shipping":"0",
   "times_used":"1",
   "is_rss":"1",
   "coupon_type":"2",
   "use_auto_generation":"0",
   "uses_per_coupon":null,
   "code":"123",
   "customer_group_ids":[ ],
   "website_ids":[ ],
   "coupon_code":"123"
}
** RAW SQL ROW
{
   "rule_id":"1",
   "name":"code123",
   "description":null,
   "from_date":"2015-06-12",
   "to_date":null,
   "uses_per_customer":"0",
   "is_active":"1",
   "conditions_serialized":"a:6:{s:4:\"type\";s:32:\"salesrule\/rule_condition_combine\";s:9:\"attribute\";N;s:8:\"operator\";N;s:5:\"value\";s:1:\"1\";s:18:\"is_value_processed\";N;s:10:\"aggregator\";s:3:\"all\";}",
   "actions_serialized":"a:6:{s:4:\"type\";s:40:\"salesrule\/rule_condition_product_combine\";s:9:\"attribute\";N;s:8:\"operator\";N;s:5:\"value\";s:1:\"1\";s:18:\"is_value_processed\";N;s:10:\"aggregator\";s:3:\"all\";}",
   "stop_rules_processing":"0",
   "is_advanced":"1",
   "product_ids":null,
   "sort_order":"0",
   "simple_action":"cart_fixed",
   "discount_amount":"12.0000",
   "discount_qty":null,
   "discount_step":"0",
   "simple_free_shipping":"0",
   "apply_to_shipping":"0",
   "times_used":"1",
   "is_rss":"1",
   "coupon_type":"2",
   "use_auto_generation":"0",
   "uses_per_coupon":"0",
   "code":"123"
}
*/

    public function dummyData()
    {
        $this->addData(array(
           'rule_id'  =>  'XPos',
           'name'  =>  'XPos',
           'description'  =>  'XPos',
           'from_date'  =>  null,
           'to_date'  =>  null,
           'uses_per_customer'  =>  '0',
           'is_active'  =>  '1',
           'stop_rules_processing'  =>  '0',
           'is_advanced'  =>  '1',
           'product_ids'  =>  null,
           'sort_order'  =>  '0',
           'simple_action'  =>  'cart_fixed',
           'discount_amount'  =>  0,
           'discount_qty'  =>  null,
           'discount_step'  =>  '0',
           'simple_free_shipping'  =>  '0',
           'apply_to_shipping'  =>  '0',
           'times_used'  =>  '1',
           'is_rss'  =>  '1',
           'coupon_type'  =>  '1',
           'use_auto_generation'  =>  '0',
           'uses_per_coupon'  =>  null,
           'code'  =>  'XPos',
           'customer_group_ids'  =>  array(),
           'website_ids'  =>  array(),
           'coupon_code'  =>  null
        ));
    }


}