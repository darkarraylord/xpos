<?php
/**
 * Created by PhpStorm.
 * User: Huy
 * Date: 6/30/2015
 * Time: 8:45 AM
 */

class SM_XPos_Block_Adminhtml_XPos_CreditMemo_Totals extends SM_XPos_Block_Adminhtml_XPos_CreditMemo_Abstract    {
    
    public function _construct() {
        $this->setTemplate('sm/xpos/creditmemo/totals.phtml');
    }

    public function getSubtotal() {
        return $this->getOrder()->formatPrice($this->getCreditmemo()->getSubtotal());
    }

    public function getShippingAmount() {
        return $this->getOrder()->formatPrice($this->getCreditmemo()->getShippingAmount());
    }

    public function getGrandTotal() {
        return $this->getOrder()->formatPrice($this->getCreditmemo()->getGrandTotal());
    }

    public function getAdjustmentRefund() {
        return $this->getOrder()->formatPrice($this->getCreditmemo()->getAdjustmentPositive());
    }

    public function getAdjustmentFee() {
        return $this->getOrder()->formatPrice($this->getCreditmemo()->getAdjustmentNegative());
    }

    public function getTax() {
        return $this->getOrder()->formatPrice($this->getCreditmemo()->getTaxAmount());
    }


}
