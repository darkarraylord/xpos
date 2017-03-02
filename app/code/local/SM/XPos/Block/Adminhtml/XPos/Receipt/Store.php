<?php
/**
 * Created by PhpStorm.
 * User: Huy
 * Date: 6/29/2015
 * Time: 4:40 PM
 */

class SM_XPos_Block_Adminhtml_XPos_Receipt_Store extends Mage_Core_Block_Template {
    /*
     * Set for development only
     */
    const DEFAULT_SLOGAN_LITERAL = "";

    const DEFAULT_HTML_ALIGNMENT = 'center';

    /*
     * General store's configurations
     * @type Array
     */
    protected $storeConfig;

    public function _construct() {
        $this->setTemplate('sm/xpos/receipt/store.phtml');
        $this->storeConfig = Mage::getStoreConfig('general/store_information', Mage::getStoreConfig('xpos/general/storeid'));
    }

    /*
     * Get store's slogan
     * @param boolean $ignore For ignoring checking enabled slogan flag
     * @return string
     */
    public function getSlogan($ignore = false) {
        if (!$ignore) {
            $sloganEnabled = Mage::getStoreConfig('xpos/receipt/slogan_enabled');
            if (empty($sloganEnabled) || $sloganEnabled == 0)
                return '';
        }
        $slogan = self::DEFAULT_SLOGAN_LITERAL;
        if (Mage::getSingleton('xpos/modes')->getIsReceiptPreviewingMode()) {
            /*
             * For previewing
             */
            if ($this->getRequest()->getParam('xpos_receipt_slogan_literal') != null) {
                $slogan = $this->getRequest()->getParam('xpos_receipt_slogan_literal');
            }
        } else {
            $slogan = Mage::getStoreConfig('xpos/receipt/slogan_literal');
        }

        return $slogan;
    }

    public function getStoreName() {
        $storeConfig = Mage::getStoreConfig('general/store_information', $this->getOrder()->getStoreId());
        return $storeConfig['name'];
    }

    /*
     * Get store's phone number
     * @return string
     */
    public function getStorePhone() {
        $storeConfig = Mage::getStoreConfig('general/store_information', $this->getOrder()->getStoreId());
        $phone = $storeConfig['phone'];
        if (!empty($phone))
            return 'PHONE # <strong>' . $phone . '</strong>';
        return '';
    }

    public function getStoreAddress() {
        $storeConfig = Mage::getStoreConfig('general/store_information', $this->getOrder()->getStoreId());
        return $storeConfig['address'];
    }

    public function getHtmlAlignment() {
        if ($this->getRequest()->getParam('xpos_receipt_store_info_html_alignment') != null) {
            /*
             * For previewing
             */
            $alignmentConfig = $this->getRequest()->getParam('xpos_receipt_store_info_html_alignment');
        } else {
            $alignmentConfig = Mage::getStoreConfig('xpos/receipt/store_info_html_alignment');
        }

        if (!empty($alignmentConfig)) {
            return $alignmentConfig;
        }
        return self::DEFAULT_HTML_ALIGNMENT;
    }
}
