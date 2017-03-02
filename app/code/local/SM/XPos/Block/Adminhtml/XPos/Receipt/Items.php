<?php

/**
 * Created by PhpStorm.
 * User: Huy
 * Date: 6/29/2015
 * Time: 8:54 PM
 */
class SM_XPos_Block_Adminhtml_XPos_Receipt_Items extends Mage_Core_Block_Template {
    const DEFAULT_INFORMATION_SEPARATOR_TYPE = '';

    public function _construct() {
        $this->setTemplate('sm/xpos/receipt/items.phtml');
    }

    /*
     * Get Order's Items
     * @return collection
     */
    public function getItems() {
        /* @var $order Mage_Sales_Model_Order*/
        $order = $this->getOrder();
        return $order->getAllItems();
    }

    public function getItemOptions($item) {
        $html = '';
        /*Option gift voucher magestore*/
        if ($item->getProductType() != 'giftvoucher')
            return false;

        $giftVouchers = Mage::getModel('giftvoucher/giftvoucher')->getCollection()->addItemFilter($item->getId());
        if ($giftVouchers->getSize()) {
            $giftVouchersCode = array();
            foreach ($giftVouchers as $giftVoucher) {
                $currency = Mage::getModel('directory/currency')->load($giftVoucher->getCurrency());
                $balance = $giftVoucher->getBalance();
                if ($currency)
                    $balance = $currency->format($balance, array(), false);
                $giftVouchersCode[] = $giftVoucher->getGiftCode() . ' (' . $balance . ') ';
            }
            $codes = implode(' ', $giftVouchersCode);
            $result[] = array(
                'label' => $this->__('Gift Card Code'),
                'value' => $codes,
                'option_value' => $codes,
            );

            foreach ($result as $k => $v) {
                $html .= '<div class="giftcard_label"><b><i>' . $v['label'] . '</i></b></div>';
                $html .= '<div class="giftcard_value">' . $v['value'] . '</div>';

            }
        }

        return $html;
    }


    public function getHtmlSeparatorStyle() {
        if ($this->getRequest()->getParam('xpos_receipt_info_separator') != null) {
            /*
             * For previewing
             */
            $separatorConfig = $this->getRequest()->getParam('xpos_receipt_info_separator');
        } else {
            $separatorConfig = Mage::getStoreConfig('xpos/receipt/info_separator');
        }

        switch ($separatorConfig) {
            case 'line':
                return 'border-top: solid 1px #000; border-bottom: solid 1px #000';
            case 'dash':
                return 'border-top: dashed 1px #000; border-bottom: dashed 1px #000';
            default:
                return self::DEFAULT_INFORMATION_SEPARATOR_TYPE;
        }
    }
}
