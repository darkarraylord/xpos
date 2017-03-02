<?php

class SM_XPos_Block_Adminhtml_Override_Ordermessage extends Mage_Adminhtml_Block_Template
{
    public function getMessages()
    {
        $message = '';
        $action = $this->getOrderAction();
        $order = Mage::getModel('sales/order')->load($this->getOrder());
        switch ($action) {
            case 'invoice':
                $GT = $order->getGrandTotal();
                $BGT = $order->getBaseGrandTotal();
                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                $this->_castValueToOrder($invoice, $BGT, $GT, $BGT, $GT);
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(false);
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
                $this->_castValueToOrder($order, $BGT, $GT, $BGT, $GT);
                $order->save();
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
                $transactionSave->save();
                $message = $this->__('The invoice has been created.');
                break;

            case 'ship':
                $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment();
                $shipment->register();
                $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($shipment)
                    ->addObject($shipment->getOrder())
                    ->save();
                $order->save();
                $message = $this->__('The shipment has been created.');
                break;

            case 'canceled':
                if (Mage::getStoreConfig('cataloginventory/options/can_back_in_stock') == 1) {
                    $order->cancel();
                }
                $order->setData('state', Mage_Sales_Model_Order::STATE_CANCELED);
                $order->setStatus("canceled");
                $order->save();
                $message = $this->__('The order has been cancelled.');
                break;
        }
        return $message;
    }

    private function _castValueToOrder($order = null, $baseGrandTotal = 0, $grandTotal = 0, $baseTotalPaid = 0, $totalPaid = 0)
    {
        $order->setBaseGrandTotal(0)
            ->setGrandTotal(0)
            ->setBaseTotalPaid(0)
            ->setTotalPaid(0);
        $order->setBaseGrandTotal($baseGrandTotal)
            ->setGrandTotal($grandTotal)
            ->setBaseTotalPaid($baseTotalPaid)
            ->setTotalPaid($totalPaid);
    }

}
