<?php
/**
 * Created by PhpStorm.
 * User: Huy
 * Date: 6/30/2015
 * Time: 9:42 AM
 */

class SM_XPos_Block_Adminhtml_XPos_Receipt_Methods extends Mage_Payment_Block_Info{
    const DEFAULT_NO_PAYMENT_METHOD_TITLE = 'No Payment information';
    const DEFAULT_NO_SHIPPING_METHOD_TITLE = 'No Shipment information';
    const DEFAULT_HTML_ALIGNMENT = 'center';

    public function _construct() {
        $this->setTemplate('sm/xpos/receipt/methods.phtml');
    }

    /*
     * SmartOSC's risk approach. Need to be cared
     * TODO: review the condition of checking if whether no payment method
     */
    public function getPaymentMethodTitle() {
        if ($this->getOrder()->getPayment() == null)
            return self::DEFAULT_NO_PAYMENT_METHOD_TITLE;
        $paymentCode = $this->getOrder()->getPayment()->getMethodInstance()->getCode();
        $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title', Mage::helper('xpos/product')->getCurrentSessionStoreId());
        return $paymentTitle;
    }

    public function getConfigDataPaymentMethod($code, $field) {
        $path = 'payment/' . $code . '/' . $field;

        return Mage::getStoreConfig($path);
    }


    /*
     * SmartOSC's risk approach. Need to be cared
     * TODO: review the condition of checking if whether no shipping method
     */
    public function getShippingMethodTitle() {
        if ($this->getOrder()->getShippingDescription() == '')
            return self::DEFAULT_NO_SHIPPING_METHOD_TITLE;
        return $this->getOrder()->getShippingDescription();
    }

    public function getHtmlAlignment() {
        if ($this->getRequest()->getParam('xpos_receipt_methods_html_alignment') != null) {
            /*
             * For previewing
             */
            $alignmentConfig = $this->getRequest()->getParam('xpos_receipt_methods_html_alignment');
        } else {
            $alignmentConfig = Mage::getStoreConfig('xpos/receipt/methods_html_alignment');
        }

        if (!empty($alignmentConfig)) {
            return $alignmentConfig;
        }
        return self::DEFAULT_HTML_ALIGNMENT;
    }

    public function getPaymentHtml()
    {
        return $this->getChildHtml('order_payment');
    }
}
