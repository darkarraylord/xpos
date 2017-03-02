<?php
/**
 * Created by PhpStorm.
 * User: Huy
 * Date: 6/29/2015
 * Time: 4:17 PM
 */

class SM_XPos_Block_Adminhtml_XPos_CreditMemo_Logo extends SM_XPos_Block_Adminhtml_XPos_CreditMemo_Abstract {
    const DEFAULT_LOGO_LITERAL = "REFUND";
    const DEFAULT_HTML_ALIGNMENT = 'center';

    public function _construct() {
        $this->setTemplate('sm/xpos/creditmemo/logo.phtml');
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
        return '<span>'. self::DEFAULT_LOGO_LITERAL .'</span>';
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
