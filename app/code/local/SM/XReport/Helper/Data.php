<?php

class SM_XReport_Helper_Data extends Mage_Core_Helper_Abstract {
    public function checkPhpVersion() {
        if (version_compare(phpversion(), '5.5.0', '<')) {
            echo 'Php version too old!';
            die();
        }
    }

    public function _formatPrice($p) {
        if (Mage::getSingleton('xreport/session')->getData('store_id') != 0)
            return Mage::helper('core')->currencyByStore(floatval($p),Mage::getSingleton('xreport/session')->getData('store_id'), true, false);
        else
            return Mage::helper('core')->currency(floatval($p), true, false);
    }

    public function getCurrentShippingTitle($_codeIn) {
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
        foreach ($methods as $_ccode => $_carrier) {
            if ($_methods = $_carrier->getAllowedMethods()) {
                foreach ($_methods as $_mcode => $_method) {
                    $_code = $_ccode . '_' . $_mcode;
                    if ($_codeIn == $_code) {
                        if (!$_title = Mage::getStoreConfig("carriers/$_mcode/title"))
                            return $_method;
                        else
                            return $_title;
                    }
                }
            }
        }

        return $_codeIn;
    }

    public function getConfigDataPaymentMethod($code, $field) {
        $path = 'payment/' . $code . '/' . $field;

        return Mage::getStoreConfig($path);
    }

    public function getAllItemsInOrder($orederId) {
        $order = Mage::getModel("sales/order")->load($orederId);
        $ordered_items = $order->getAllVisibleItems(); //Difference  getAllItems()
        $arrayItems = array();
        foreach ($ordered_items as $item) {
            $arrayItems[] = array(
                'id' => $item->getItemId(),
                'sku' => $item->getSku(),
                'qty' => $item->getQtyOrdered(),
                'name' => $item->getName(),
                'price' => $this->_formatPrice($item->getData('price')),
                'tax' => $this->_formatPrice($item->getData('tax_amount')),
                'price_incl_tax' => $this->_formatPrice($item->getData('price_incl_tax'))
            );
        }
        return $arrayItems;
    }

    public function getAllShippingMethods() {
        $methods = Mage::getSingleton('shipping/config')->getActiveCarriers();

        $options = array();
        $_methodOptions = array();
        $_methodOptions[] = array(
            'label' => 'Please Select..',
            'value' => ''
        );
        foreach ($methods as $_ccode => $_carrier) {

            if ($_methods = $_carrier->getAllowedMethods()) {
                foreach ($_methods as $_mcode => $_method) {
                    $_code = $_ccode . '_' . $_mcode;
                    $_methodOptions[] = array('value' => $_code, 'label' => $_method);
                }

//                if (!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
//                    $_title = $_ccode;
//
//                $options[] = array('value' => $_methodOptions, 'label' => $_title);
            }
        }

        return Mage::helper('core')->jsonEncode($_methodOptions);
    }

    public function getActivePaymentMethods() {
        $payments = Mage::getSingleton('payment/config')->getActiveMethods();

//        $methods = array(array('value'=>'', 'label'=>Mage::helper('adminhtml')->__('--Please Select--')));
        $methods = array();
        $methods[] = array(
            'label' => 'Please Select..',
            'value' => ''
        );
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = Mage::getStoreConfig('payment/' . $paymentCode . '/title');
            if ($paymentCode == 'free' || $paymentCode == 'xpaymentMultiple') continue;
            $methods[$paymentCode] = array(
                'label' => $paymentTitle,
                'value' => $paymentCode,
            );
        }

        return Mage::helper('core')->jsonEncode($methods);

    }
}
