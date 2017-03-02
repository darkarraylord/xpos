<?php

/**
 * Created by PhpStorm.
 * User: vjcspy
 * Date: 7/2/15
 * Time: 4:43 PM
 */
class SM_XPayment_Model_ConfigPaymentAllow
{
    public function toOptionArray()
    {

        $arrayPaymentAllow = Mage::helper('xpayment/data')->getPaymentAllowSplit();
//        $result[] = array('value' => 'code', 'label' => 'Title');
        foreach ($arrayPaymentAllow as $code) {
            $result[] = array('value' => $code, 'label' => $this->getConfigData($code, 'title'));
        }

        return $result;
    }

    public function getConfigData($code, $field, $storeId = null)
    {
        if ($code == 'cashondelivery') {
            $path = 'payment/' . $code . '/' . $field;

            return Mage::getStoreConfig($path, $storeId);
        } else {
            $path = 'xpayment/' . $code . '/' . $field;

            return Mage::getStoreConfig($path, $storeId);
        }
    }
}


