<?php
/**
 * Created by PhpStorm.
 * User: Huy
 * Date: 6/29/2015
 * Time: 4:17 PM
 */

class SM_XPos_Block_Adminhtml_XPos_Receipt_Logo extends Mage_Core_Block_Template {
    const DEFAULT_LOGO_LITERAL = "INVOICE";
    const DEFAULT_HTML_ALIGNMENT = 'center';

    public function _construct() {
        $this->setTemplate('sm/xpos/receipt/logo.phtml');
    }

    public function setLogo($logoSrc, $logoAlt) {
        $this->setLogoSrc($logoSrc);
        $this->setLogoAlt($logoAlt);
        return $this;
    }

    public function getLogoSrc() {
        return (empty($this->_data['logo_src'])) ? $this->getSkinUrl($this->_data['logo_src']) : false;
    }

    public function getLogo() {
        $logoSrc = Mage::helper('xpos/configXPOS')->getLogoImageFile();
        if (empty($logoSrc)) {
            if ($this->getRequest()->getParam('xpos_receipt_logo_literal') != null) {
                /*
                 * For previewing
                 */
                $logoLiteral = $this->getRequest()->getParam('xpos_receipt_logo_literal');
            } else {
                $logoLiteral = Mage::getStoreConfig('xpos/receipt/logo_literal');
            }

            if (empty($logoLiteral)) {
                return '<span>'. self::DEFAULT_LOGO_LITERAL .'</span>';
            }
            return '<span>'. $logoLiteral .'</span>';
        }
        return '<img src="' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . DIRECTORY_SEPARATOR . 'receipt' . DIRECTORY_SEPARATOR . $logoSrc . '" />';
    }

    public function getHtmlAlignment() {
        if ($this->getRequest()->getParam('xpos_receipt_logo_html_alignment') != null) {
            /*
             * For previewing
             */
            $alignmentConfig = $this->getRequest()->getParam('xpos_receipt_logo_html_alignment');
        } else {
            $alignmentConfig = Mage::getStoreConfig('xpos/receipt/logo_html_alignment');
        }

        if (!empty($alignmentConfig)) {
            return $alignmentConfig;
        }
        return self::DEFAULT_HTML_ALIGNMENT;
    }
}
