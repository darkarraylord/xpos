<?php

/**
 * Created by PhpStorm.
 * User: sonbv
 * Date: 2/11/15
 * Time: 5:14 PM
 */
class SM_XPos_Model_Config_DefaultShipping
    extends Mage_Shipping_Model_Config
{
    public function toOptionArray()
    {
//        $store = null;
//        $carriers = array();
//        $config = Mage::getStoreConfig('carriers', $store);
//        foreach ($config as $code => $carrierConfig) {
//            if (Mage::getStoreConfigFlag('carriers/' . $code . '/active', $store)) {
//                $carrierModel = $this->_getCarrier($code, $carrierConfig, $store);
//                if ($carrierModel) {
//                    $carriers[] = array(
//                        'value' => $code,
//                        'label' => Mage::getStoreConfig('carriers/' . $code . '/title', $store) . ': '
//                            . Mage::getStoreConfig('carriers/' . $code . '/name', $store)
//                    );
//                }
//            }
//        }
//        return $carriers;
        return $this->getAllShippingMethods();
    }

    public function getAllShippingMethods()
    {
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();

        $options = array();

        foreach ($methods as $_ccode => $_carrier) {
            $_methodOptions = array();
            if ($_methods = $_carrier->getAllowedMethods()) {
                foreach ($_methods as $_mcode => $_method) {
                    $_code = $_ccode . '_' . $_mcode;
                    $_methodOptions[] = array('value' => $_code, 'label' => $_method);
                }

                if (!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
                    $_title = $_ccode;

                $options[] = array('value' => $_methodOptions, 'label' => $_title);
            }
        }

        return $options;
    }
}
