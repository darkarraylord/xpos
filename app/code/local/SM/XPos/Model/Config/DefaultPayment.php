<?php
/**
 * Created by PhpStorm.
 * User: sonbv
 * Date: 2/11/15
 * Time: 5:14 PM
 */

class SM_XPos_Model_Config_DefaultPayment
    extends Mage_Payment_Model_Config
{
    public function toOptionArray()
    {
        /*create new method. Older code below*/
//        $store = Mage::helper('xpos')->getXPosStoreId();
//        $methods = array();
//        $config = Mage::getStoreConfig('payment', $store);
//        $paypalConfig = Mage::getModel('paypal/config')->setStoreId($store);
//        foreach ($config as $code => $methodConfig) {
//            if (!Mage::getStoreConfigFlag('payment/'.$code.'/active', $store)) continue;
//
//            if ($this->isPaypalMethod($methodConfig)) {
//                if (!$paypalConfig->isMethodAvailable($code)) continue;
//            }
//
//            if (array_key_exists('model', $methodConfig)) {
//                $methodModel = Mage::getModel($methodConfig['model']);
//                if ($methodModel && $methodModel->getConfigData('active', $store)) {
//                    $methods[] = array(
//                        'value' => $code,
//                        'label' => Mage::getStoreConfig('payment/'.$code.'/title', $store)
//                    );
//                }
//            }
//
//        }
//        return $methods;

        return $this->getActivePaymentMethods();
    }

    public function getActivePaymentMethods()
    {
        $payments = Mage::getSingleton('payment/config')->getActiveMethods();

//        $methods = array(array('value'=>'', 'label'=>Mage::helper('adminhtml')->__('--Please Select--')));
        $methods = array();
        foreach ($payments as $paymentCode=>$paymentModel) {
            $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');
            if($paymentCode == 'free' || $paymentCode == 'xpaymentMultiple') continue;
            $methods[$paymentCode] = array(
                'label'   => $paymentTitle,
                'value' => $paymentCode,
            );
        }

        return $methods;

    }

    protected function isPaypalMethod($methodConfig)
    {
        return (array_key_exists('group', $methodConfig) && $methodConfig['group'] === 'paypal');
    }
}
