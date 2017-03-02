<?php
/**
 * Created by PhpStorm.
 * User: vjcspy
 * Date: 6/17/15
 * Time: 11:34 AM
 */
class SM_XPayment_Block_Form_XpaymentMultiple extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('xpayment/form/xpaymentMultiple.phtml');
    }

    public function getQuote()
    {
        $session = Mage::getSingleton('adminhtml/session_quote');
        return $quote = $session->getQuote();
    }

    public function getCheckOutSession()
    {
        /*NOt RUN*/
        return $session = Mage::getSingleton('checkout/session');
    }

    public function getPaymentsCanSplit(){
        $payments = Mage::getStoreConfig('xpayment/xpaymentMultiple/payment_allow');
        $payments = explode(",", $payments);
        return $payments;
    }
    public function getConfigData($code,$field, $storeId = null)
    {
        $path = 'xpayment/'.$code.'/'.$field;
        return Mage::getStoreConfig($path, $storeId);
    }
}
