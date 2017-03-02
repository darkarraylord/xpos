<?php
/**
 * Created by PhpStorm.
 * User: Huy
 * Date: 6/29/2015
 * Time: 6:36 PM
 */

class SM_XPos_Block_Adminhtml_XPos_CreditMemo_Root extends SM_XPos_Block_Adminhtml_XPos_CreditMemo_Abstract {
    const DEFAULT_FONT_TYPE = 'monospace';

    /*
     * Page's font
     * @type string
     */
    protected $font;

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
