<?php

/**
 * Created by PhpStorm.
 * User: vjcspy
 * Date: 6/18/15
 * Time: 6:19 PM
 */
class SM_XPos_Block_Adminhtml_Sales_Order_Create_Billing_Method_Form extends Mage_Adminhtml_Block_Sales_Order_Create_Billing_Method_Form
{

    public function isEnableSplitPayment()
    {
        $config = Mage::getStoreConfig('xpayment/xpaymentMultiple/active');
        if (!!$config) {
            return true;
        } else {
            return false;
        }
    }

    public function getPaymentAllowSplit()
    {
        $payments = Mage::getStoreConfig('xpayment/xpaymentMultiple/payment_allow');
        $payments = explode(",", $payments);
        return $payments;
    }
}
