<?php
class SM_XPayment_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getPaymentAllowSplit(){
        $paymentArray = array('xpayment_cashpayment', 'xpayment_ccpayment','xpayment_cc1payment','xpayment_cc2payment','xpayment_cc3payment','xpayment_cc4payment');
        $paymentAllowArray = array();
        foreach($paymentArray as $paymentCode){
            if(Mage::getStoreConfig('xpayment/'.$paymentCode.'/active')){
                $paymentAllowArray[] = $paymentCode;
            }
        }
        return $paymentAllowArray;
    }

    public function setConfig($path, $string)
    {
        try {
            Mage::getModel('core/config_data')
                ->load($path, 'path')
                ->setValue($string)
                ->setPath($path)
                ->save();
        } catch (Exception $e) {
            throw new Exception(Mage::helper('cron')->__('Unable to save the config data with path ' . $path));
        }
    }
}
