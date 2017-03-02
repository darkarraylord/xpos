<?php
require_once('Mage/Adminhtml/controllers/Sales/Order/CreditmemoController.php');
class SM_XPos_Adminhtml_AjaxcreditmemoController extends Mage_Adminhtml_Sales_Order_CreditmemoController
{
    public function saveAction()
    {
        $result = array();
        $data = $this->getRequest()->getPost('creditmemo');
        Mage::getSingleton('adminhtml/session')->setData("credit_data",$data);
        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }

        try {
            $creditmemo = $this->_initCreditmemo();
            if ($creditmemo) {
                if (($creditmemo->getGrandTotal() <=0) && (!$creditmemo->getAllowZeroGrandTotal())) {
                    $result['status'] = '0';
                    $result['messages'] = $this->__('Credit memo\'s total must be positive.');
                }

                $comment = '';
                if (!empty($data['comment_text'])) {
                    $creditmemo->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );
                    if (isset($data['comment_customer_notify'])) {
                        $comment = $data['comment_text'];
                    }
                }

                if (isset($data['do_refund'])) {
                    $creditmemo->setRefundRequested(true);
                }
                if (isset($data['do_offline'])) {
                    $creditmemo->setOfflineRequested((bool)(int)$data['do_offline']);
                }

                $creditmemo->register();
                if (!empty($data['send_email'])) {
                    $creditmemo->setEmailSent(true);
                }

                $creditmemo->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
                $this->_saveCreditmemo($creditmemo);
                $creditmemoId = $creditmemo->getId();
                $creditmemo->sendEmail(!empty($data['send_email']), $comment);
                $data = $this->getRequest()->getParams();
                if (isset($data['refund_from_drawer']) && $data['refund_from_drawer'] == 1) {
                    $this->_refundCash($data, $creditmemo);
                }
                $result['status'] = '1';
                $result['messages'] = $this->__('The credit memo has been created.');
                $result['creditmemo_id'] = $creditmemoId;
                Mage::getSingleton('adminhtml/session')->getCommentText(true);

            } else {
                $this->_forward('noRoute');
                return;
            }
        } catch (Mage_Core_Exception $e) {
            $result['status'] = '0';
            $result['messages'] = $e->getMessage();
            Mage::getSingleton('adminhtml/session')->setFormData($data);
        } catch (Exception $e) {
            Mage::logException($e);
            $result['status'] = '0';
            $result['messages'] = $this->__('Cannot save the credit memo.');
        }
        echo json_encode($result);
    }
    public function printAction(){
        $data = $this->getRequest()->getPost();
        $creditMemos = Mage::getResourceModel('sales/order_creditmemo_collection');
        $creditMemos->addFieldToFilter('order_id', $data['order_id']);
        $result = array();
        $incrementIds = array();
            foreach($creditMemos->getData() as $value){
                $incrementIds[] = $value['entity_id'];
            }
        $result['increment_ids'] = $incrementIds;
        echo json_encode($result);
    }

    protected function _refundCash($data, $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $totalRefund = $order->getTotalRefunded();
        $totalPaid = $order->getTotalPaid();
        $payment = $method = $order->getPayment();
        $method = $payment->getMethod();
        $arrayAllow = Mage::helper('xpos')->getListDrawerPayment();
        $hasDrawerPayment = false;
        $totalPaidWithDrawerPayment = 0;
        if ($method == 'xpaymentMultiple') {
            $splitData = @unserialize($payment->getAdditionalData());
            foreach ($splitData as $methodCode => $cash) {
                if (in_array($methodCode, $arrayAllow)) {
                    $hasDrawerPayment = true;
                    $totalPaidWithDrawerPayment += $cash;
                }
            }
        }
        if ($hasDrawerPayment) {
            $totalRefund = $totalPaidWithDrawerPayment - ($totalPaid - $totalRefund);
        }
        $transaction = Mage::getModel('xpos/transaction');
        $transaction->saveTransaction(
            $data_transaction = array(
                'type'         => 'refund',
                'method'       => $method,
                'cash_in'      => 0,
                'cash_out'     => 0,
                'amount'       => $totalRefund,
                'till_id'      => $data['till_id'],
                'warehouse_id' => $data['warehouse_id'],
                'xpos_user_id' => $data['xpos_user_id'],
                'order_id'     => $order->getIncrementId(),
            )
        );

    }
}