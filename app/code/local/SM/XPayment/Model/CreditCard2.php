<?php
/**
 * Created by PhpStorm.
 * User: vjcpsy
 * Date: 6/25/2015
 * Time: 11:31
 */
class SM_XPayment_Model_CreditCard2 extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'xpayment_cc2payment';
    protected $_canUseInternal = true;
    protected $_canUseCheckout = false;
    protected $_canUseForMultishipping = false;
    //protected $_isGateway = true;
    //protected $_canAuthorize = true;

    public function authorize(Varien_Object $payment, $amount) {
        Mage::log("Dummypayment\tIn authorize");
        return $this;
    }
}
