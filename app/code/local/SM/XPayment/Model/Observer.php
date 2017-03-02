<?php

/**
 * Created by PhpStorm.
 * User: vjcspy
 * Date: 7/24/15
 * Time: 10:49 AM
 */
class SM_XPayment_Model_Observer extends Mage_Core_Model_Abstract
{
    const STRING_PATH_CC1 = 'payment/xpayment_cc1payment/';
    const STRING_PATH_CC2 = 'payment/xpayment_cc2payment/';
    const STRING_PATH_CASHPAYMENT = 'payment/xpayment_cashpayment/';
    const STRING_PATH_CCPAYMENT = 'payment/xpayment_ccpayment/';
    const STRING_PATH_CC3 = 'payment/xpayment_cc3payment/';
    const STRING_PATH_SPAYMENT = 'payment/xpaymentMultiple/';
    const STRING_PATH_CC4 = 'payment/xpayment_cc4payment/';
    const STRING_PATH_IZETTLE = 'payment/xpayment_izettlepayment/';
    const STRING_PATH_STRIPE = 'payment/xpayment_stripepayment/';
    const STRING_PATH_PAYPAL = 'payment/xpayment_paypalpayment/';
    const STRING_PATH_BLUEPAY = 'payment/xpayment_bluepaypayment/';
    const STRING_PATH_AUTHORIZE = 'payment/xpayment_authorizepayment/';

    public function handle_adminSystemConfigChangedSectionSPayment()
    {
        $cc1Config = Mage::getStoreConfig('xpayment/xpayment_cc1payment');
        $this->setDataToPaymentConfig(self::STRING_PATH_CC1, $cc1Config);

        $cc2Config = Mage::getStoreConfig('xpayment/xpayment_cc2payment');
        $this->setDataToPaymentConfig(self::STRING_PATH_CC2, $cc2Config);

        $cashConfig = Mage::getStoreConfig('xpayment/xpayment_cashpayment');
        $this->setDataToPaymentConfig(self::STRING_PATH_CASHPAYMENT, $cashConfig);

        $ccCashConfig = Mage::getStoreConfig('xpayment/xpayment_ccpayment');
        $this->setDataToPaymentConfig(self::STRING_PATH_CCPAYMENT, $ccCashConfig);

        $cc3Config = Mage::getStoreConfig('xpayment/xpayment_cc3payment');
        $this->setDataToPaymentConfig(self::STRING_PATH_CC3, $cc3Config);

        $cc4Config = Mage::getStoreConfig('xpayment/xpayment_cc4payment');
        $this->setDataToPaymentConfig(self::STRING_PATH_CC4, $cc4Config);

        $multipleConfig = Mage::getStoreConfig('xpayment/xpaymentMultiple');
        $this->setDataToPaymentConfig(self::STRING_PATH_SPAYMENT, $multipleConfig);

        $izettleConfig = Mage::getStoreConfig('xpayment/xpayment_izettlepayment');
        $this->setDataToPaymentConfig(self::STRING_PATH_IZETTLE, $izettleConfig);

        $stripeConfig = Mage::getStoreConfig('xpayment/xpayment_stripepayment');
        $this->setDataToPaymentConfig(self::STRING_PATH_STRIPE, $stripeConfig);

        $paypalConfig = Mage::getStoreConfig('xpayment/xpayment_paypalpayment');
        $this->setDataToPaymentConfig(self::STRING_PATH_PAYPAL, $paypalConfig);

        $bluepayConfig = Mage::getStoreConfig('xpayment/xpayment_bluepaypayment');
        $this->setDataToPaymentConfig(self::STRING_PATH_BLUEPAY, $bluepayConfig);

        $authorizeConfig = Mage::getStoreConfig('xpayment/xpayment_authorizepayment');
        $this->setDataToPaymentConfig(self::STRING_PATH_AUTHORIZE, $authorizeConfig);
    }

    protected function setDataToPaymentConfig($path, $data)
    {
        if(isset($data['sort_order'])){$this->setConfig($path . 'active', $data['active']);}
        if(isset($data['active'])){$this->setConfig($path . 'active', $data['active']);}
        if(isset($data['title'])){ $this->setConfig($path . 'title', $data['title']);}
        if(isset($data['order_status'])){$this->setConfig($path . 'order_status', $data['order_status']);}
        if(isset($data['allowspecific'])){$this->setConfig($path . 'allowspecific', $data['allowspecific']);}
        if(isset($data['sort_order'])){ $this->setConfig($path . 'sort_order', $data['sort_order']);}
        if(isset($data['specificcountry'])) {$this->setConfig($path . 'specificcountry', $data['specificcountry']);}
    }

    protected function getConfig($path)
    {
        return Mage::getModel('core/config_data')->load($path, 'path');
    }

    protected function setConfig($path, $string)
    {
        Mage::helper('xpayment/data')->setConfig($path, $string);
    }
}
