<?php
/**
 * Created by PhpStorm.
 * User: sonbv
 * Date: 28/08/2015
 * Time: 15:56
 */

class SM_XPos_Block_Adminhtml_Sales_Order_View_Info_Refund extends Mage_Adminhtml_Block_Template
{

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function hasInvoiceToRefund()
    {
        foreach ($this->getOrder()->getInvoiceCollection() as $invoice) {
            if ($this->canRefund($invoice)) return true;
        }
        return false;
    }

    /**
     * @param $invoice Mage_Sales_Model_Order_Invoice
     * @return bool
     */
    public function canRefund($invoice)
    {
        $orderPayment = $invoice->getOrder()->getPayment();
        /*
         * copy from Mage_Adminhtml_Block_Sales_Order_Invoice_View LINE 76
         * */
        if ($this->_isAllowedAction('creditmemo') && $invoice->getOrder()->canCreditmemo()) {
            if (($orderPayment->canRefundPartialPerInvoice()
                    && $invoice->canRefund()
                    && $orderPayment->getAmountPaid() > $orderPayment->getAmountRefunded())
                || ($orderPayment->canRefund() && !$invoice->getIsUsedForRefund())) {
                return true;
            }
        }
        return false;
    }

    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/' . $action);
    }


}