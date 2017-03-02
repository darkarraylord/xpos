<?php
/**
 * Created by PhpStorm.
 * User: Huy
 * Date: 6/29/2015
 * Time: 6:36 PM
 */

class SM_XPos_Block_Adminhtml_XPos_Receipt_Root extends Mage_Core_Block_Template {
    /**
     *
     */
    const DEFAULT_FONT_TYPE = 'monospace';
    /*
     * Current Order
     * @type Mage_Sales_Order
     */
    /**
     * @var Mage_Sales_Model_Order
     */
    protected $order;
    /*
     * Page's font
     * @type string
     */
    /**
     * @var
     */
    protected $font;

    /**
     * @throws Exception
     */
    public function _construct(){
        /*
         * SmartOSC's approach to get current order
         */
        $infoOrder = Mage::getSingleton('adminhtml/session')->getInfoOrder();
        if($infoOrder == null){
            $orderId = Mage::getSingleton('adminhtml/session')->getOrderViewDetail();
        } else {
            $orderId = $infoOrder['entity_id'];
        }
        if($orderId == null){
            $orderId = $this->getRequest('')->getParam('order_id');
        }
        $this->order = Mage::getModel('sales/order')->load($orderId);
    }

    /**
     * @return mixed
     */
    public function getOrder() {
        return $this->order;
    }

    /**
     * @return mixed|string
     * @throws Exception
     */
    public function getFont() {
        if ($this->getRequest()->getParam('xpos_receipt_font_type') != null) {
            /*
             * For previewing
             */
            $font = $this->getRequest()->getParam('xpos_receipt_font_type');
        } else {
            $font = Mage::getStoreConfig('xpos/receipt/font_type');
        }

        return (!empty($font)) ? $font : self::DEFAULT_FONT_TYPE;
    }
}
