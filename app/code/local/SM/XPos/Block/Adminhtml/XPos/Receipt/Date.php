<?php
/**
 * Created by PhpStorm.
 * User: Huy
 * Date: 6/29/2015
 * Time: 6:00 PM
 */

class SM_XPos_Block_Adminhtml_XPos_Receipt_Date extends Mage_Core_Block_Template {
    const DEFAULT_DAY_FORMAT = 'm/d/Y';
    const DEFAULT_TIME_FORMAT = 'H:i:s';

    public function _construct() {
        $this->setTemplate('sm/xpos/receipt/date.phtml');
    }

    public function getDay() {
        if ($this->getRequest()->getParam('xpos_receipt_day_format') != null) {
            /*
             * For previewing
             */
            $dayFormat = $this->getRequest()->getParam('xpos_receipt_day_format');
        } else {
            $dayFormat = Mage::getStoreConfig('xpos/receipt/day_format');
        }
        $dayFormat = empty($dayFormat) ? self::DEFAULT_DAY_FORMAT : $dayFormat;
        return date($dayFormat, Mage::getModel('core/date')->timestamp($this->getOrder()->getCreatedAt()));
    }

    public function getTime() {
        return date(self::DEFAULT_TIME_FORMAT, Mage::getModel('core/date')->timestamp($this->getOrder()->getCreatedAt()));
    }
}