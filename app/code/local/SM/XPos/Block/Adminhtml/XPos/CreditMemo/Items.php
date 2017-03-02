<?php
/**
 * Created by PhpStorm.
 * User: Huy
 * Date: 6/29/2015
 * Time: 8:54 PM
 */

class SM_XPos_Block_Adminhtml_XPos_CreditMemo_Items extends SM_XPos_Block_Adminhtml_XPos_CreditMemo_Abstract {
    const DEFAULT_INFORMATION_SEPARATOR_TYPE = '';

    public function _construct() {
        $this->setTemplate('sm/xpos/creditmemo/items.phtml');
    }

    /*
     * Get Order's Items
     * @return collection
     */
    public function getItems() {
        return $this->getOrder()->getItemsCollection();
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
