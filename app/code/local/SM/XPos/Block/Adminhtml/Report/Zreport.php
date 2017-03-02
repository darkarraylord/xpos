<?php

/**
 * Created by PhpStorm.
 * User: Smartor
 * Date: 10/15/14
 * Time: 9:39 AM
 */
class SM_XPos_Block_Adminhtml_Report_Zreport extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        $this->setTemplate('sm/xpos/report/zreport.phtml');
        parent::_construct();
    }

    public function getTillId()
    {
        $till_id = $this->getRequest()->getParam('till_id');
        Mage::getSingleton('adminhtml/session')->setTillInfo($till_id);
        return $till_id;
    }

    public function getTillName()
    {
        $till_id = $this->getRequest()->getParam('till_id');
        $tillName = Mage::getModel('xpos/till')->load($till_id)->getData('till_name');
        Mage::getSingleton('adminhtml/session')->setTillInfo($till_id);
        return $tillName;
    }

    public function getPaymentPaidInfo()
    {
        $data = array();

        if (Mage::helper('xpos/configXPOS')->getEnableTill() === '1') {
            $till_id = $this->getRequest()->getParam('till_id');
        } else {
            $till_id = NULL;
        }

        if (Mage::helper('xpos/configXPOS')->getIntegrateXmwhEnable()) {
            $warehouse_id = Mage::getSingleton('admin/session')->getWarehouseId();
            Mage::getSingleton('adminhtml/session')->setWarehouseReport($warehouse_id);
        } else $warehouse_id = NULL;

        ($till_id === NULL) ? $report_collec = $this->_getReportCollection(0) : $report_collec = $this->_getReportCollection($till_id);

        if (count($report_collec) > 0) {
            $fist_item = $report_collec->getFirstItem();
            $previous_time = $fist_item->getData('created_time');
        } else $previous_time = '2014-01-01 01:01:01';

        $collection = $this->_getXPosOrderCollection($till_id, $previous_time, false);

        $data['other_payment']['num_order_total'] = count($collection);
        $data['other_payment']['previous_time'] = $previous_time;
        $data['other_payment']['grand_order_total'] = 0;
        $data['other_payment']['tax_order_total'] = 0;
        $data['other_payment']['total_refund'] = 0;
        $payment_arr = array();
        $data['other_payment']['money_system'] = 0;
        $data['other_payment']['order_count'] = 0;
        $data['other_payment']['transac_in'] = 0;
        $data['other_payment']['transac_out'] = 0;

        //Get transaction info of day
        $default_transfer = Mage::getStoreConfig('xpos/report/default_transfer');
        //getReal Current balance of till
        if ($till_id === NULL) {
            $transacs = $this->_getXPosTransacCollection(0, $previous_time);
        } else {
            $transacs = $this->_getXPosTransacCollection($till_id, $previous_time);
        }

        $real_current_balance = $transacs->getFirstItem()->getData('current_balance');
        $previous_transfer = $transacs->getLastItem()->getData('current_balance');

        if ($till_id === NULL) {
            $transac_collection = $this->_getManualXPosTransacCollection(0, $previous_time);
        } else {
            $transac_collection = $this->_getManualXPosTransacCollection($till_id, $previous_time);
        }

        $current_balance = $transac_collection->getFirstItem()->getData('current_balance');

        foreach ($transac_collection as $transaction) {
            if ($transaction->getData('type') == 'in') {
                $data['other_payment']['transac_in'] += floatval($transaction->getData('cash_in'));
            } else {
                $data['other_payment']['transac_out'] += floatval($transaction->getData('cash_out'));
            }
        }

        if ($previous_time !== '2014-01-01 01:01:01')
            $amount_diff = $data['other_payment']['transac_in'] - $data['other_payment']['transac_out'] - $previous_transfer;
        else {
            $amount_diff = $data['other_payment']['transac_in'] - $data['other_payment']['transac_out'];
            $previous_transfer = 0;
        }

        $grandTotalOrder = 0;
        $data['split_total'] = 0;
        // caculate Amount
        $allowSplitPaymentList = array();
        if(is_array(Mage::getStoreConfig('xpayment'))){
            $allowSplitPaymentList = array_keys(Mage::getStoreConfig('xpayment'));
        }
        $otherPaymentList = array('ccsave', 'authorizenet', 'cashondelivery', 'checkmo');
        $cashGroup = array('xpayment_cashpayment', 'cashondelivery');
        $ccGroup = array('ccsave', 'xpayment_ccpayment', 'authorizenet');
        $allowPaymentList = array_merge($allowSplitPaymentList, $otherPaymentList);
        if (count($collection) > 0)
            foreach ($collection as $order) {
                if (floatval($order->getData('base_total_paid')) == 0 && (float)$order->getData('base_gift_cards_amount') == 0) {
                    continue;
                }
                $grandTotal = $order->getData('grand_total');
                $totalTax = $order->getData('tax_amount');
                $totalRefund = $order->getData('total_refunded');
                $totalTaxRefund = $order->getData('tax_refunded');
                /*
                 * real cash till counted = grand - refund;
                 * real tax = tax order - tax refund
                 * real discount same as
                 * */
                $data['other_payment']['total_refund'] += floatval($totalRefund);
                $counted = $grandTotal - $totalRefund;
                $realTax = $totalTax - $totalTaxRefund;
                $data['other_payment']['grand_order_total'] += floatval($counted);
                $data['other_payment']['tax_order_total'] += floatval($realTax);
                $payment = $order->getPayment()->getMethod();

                if (
                    in_array($payment, $allowPaymentList)
                    || Mage::helper('xpos/configXPOS')->getShowAdditionInEndOfDayReport()
                    || $payment === 'xpaymentMultiple'
                ) {
                    $hasSplit = false;
                    $splitData = array();
                    switch ($payment) {
                        case 'xpaymentMultiple':
                            $hasSplit = true;
                            break;
                        default:
                            $data[$payment]['payment_name'] = $paymentTitle = Mage::getStoreConfig('payment/' . $payment . '/title');
                            break;
                    }

                    if (!$hasSplit) {
                        isset($data[$payment]['money_system']) ? $data[$payment]['money_system'] += floatval($counted) : $data[$payment]['money_system'] = floatval($counted);
                        isset($data[$payment]['order_count']) ? $data[$payment]['order_count'] += 1 : $data[$payment]['order_count'] = 1;
                        $grandTotalOrder += $counted;
//                        }
                    } else {
                        $arrayNotShow = array('method', 'enable', 'check_no', 'check_date', 'checks');
                        $details = @unserialize($order->getPayment()->getAdditionalData());
                        if ($details !== null) {
                            $splitData = $details;
                        }

                        $splitAmount = 0;
                        foreach ($splitData as $k => $v) {
                            if (in_array($k, $arrayNotShow)) {
                                continue;
                            }
                            /*
                             * if has refund then TP = 0
                             * */
                            $cash = floatval($v);
                            if ($totalRefund > 0) {
                                $cash = 0;
                            }
                            if ($k != 'xpayment_discount')
                                $splitAmount += floatval($v);

                            $data[$k]['payment_name'] = Mage::getStoreConfig('payment/' . $k . '/title');;
                            isset($data[$k]['money_system']) ? $data[$k]['money_system'] += $cash : $data[$k]['money_system'] = $cash;
                            isset($data[$k]['order_count']) ? $data[$k]['order_count'] += 1 : $data[$k]['order_count'] = 1;
                        }
                        if ($splitAmount > $grandTotal && $totalRefund == 0) {
                            $v = $grandTotal - $splitAmount;
                            isset($data['xpayment_cashpayment']['money_system']) ? $data['xpayment_cashpayment']['money_system'] += $v : $data['xpayment_cashpayment']['money_system'] = $v;
                        }
                        isset($data['split_total']) ? $data['split_total'] += $counted : $data['split_total'] = $counted;
                    }
                } else {
                    $data['other_payment']['payment_name'] = 'Other Payments';
                    $data['other_payment']['money_system'] += floatval($counted);
                    $data['other_payment']['order_count']++;
                    $grandTotalOrder += $counted;
                }
            }


        //Count cash(transfer info)
        $total = $current_balance;
        if (isset($data['xpayment_cashpayment']['money_system'])) {
            $total = $current_balance + $data['xpayment_cashpayment']['money_system'];
        }

        $result = $total + $amount_diff;
        $data['xpayment_cashpayment']['payment_name'] = 'Cash';
        $data['xpayment_cashpayment']['money_system'] = $result;
        $data['xpayment_cashpayment']['total'] = $total;
        $data['xpayment_cashpayment']['in_out'] = $amount_diff;
        $data['xpayment_cashpayment']['previous_transfer'] = $previous_transfer;

//        $data['other_payment']['grand_order_total'] += $previous_transfer + $amount_diff;
        $data['other_payment']['grand_order_total'] = $grandTotalOrder;
        $data['other_payment']['till_current'] = $real_current_balance;

        // xpayment_cashpayment should be the last in list
        $cashpayment = $data['xpayment_cashpayment'];
        unset($data['xpayment_cashpayment']);
        $data['xpayment_cashpayment'] = $cashpayment;

        Mage::getSingleton('adminhtml/session')->setPaymentInfo($data);
        return $data;
    }

    public function getDiscountPaidInfo()
    {
        $report_collec = Mage::getModel('xpos/report')->getCollection()
            ->addOrder('report_id', 'DESC');
        if (count($report_collec) > 0) {
            $fist_item = $report_collec->getFirstItem();
            $previous_time = $fist_item->getData('created_time');
        } else $previous_time = '2014-01-01 01:01:01';

        $data = array();
        $data['discount_amount'] = 0;
        $data['order_count'] = 0;
        $data['voucher'] = 0;
        $data['voucher_orders'] = 0;

        if (Mage::helper('xpos/configXPOS')->getEnableTill() === '1') {
            $till_id = $this->getRequest()->getParam('till_id');
        } else {
            $till_id = NULL;
        }

        $collection = $this->_getXPosOrderCollection($till_id, $previous_time);
        if (count($collection) > 0)
            foreach ($collection as $order) {
                $this->_arrCurrentOrders[] = $order->getData('entity_id');
                if (Mage::getEdition() == "Enterprise") {
                    if ((float)$order->getData('base_customer_balance_amount') || (float)$order->getData('base_gift_cards_amount')) {
                        $data['voucher_orders']++;
                    }
                    $data['voucher'] += floatval($order->getData('base_customer_balance_amount'));
                    $data['voucher'] += floatval($order->getData('base_gift_cards_amount'));
                }
                /*
                 * real = order - refund
                 * */
                $discount = $order->getData('discount_amount');
                $discountRefund = $order->getData('discount_refunded');
                $realDiscount = $discount - $discountRefund;
                $data['discount_amount'] += floatval($realDiscount);
                if (abs($realDiscount) > 0) {
                    $data['order_count']++;
                }
            }

        Mage::getSingleton('adminhtml/session')->setDiscountInfo($data);
        return $data;
    }


    private $_arrCurrentOrders = array();

    public function getRefundTotal()
    {
        $report_collec = Mage::getModel('xpos/report')->getCollection()
            ->addOrder('report_id', 'DESC');
        if (count($report_collec) > 0) {
            $fist_item = $report_collec->getFirstItem();
            $previous_time = $fist_item->getData('created_time');
        } else $previous_time = '2014-01-01 01:01:01';

        $data = array();
        $data['refund_amount'] = 0;
        $data['refund_from_cash_drawer'] = 0;
        $data['order_count'] = 0;

        if (Mage::helper('xpos/configXPOS')->getEnableTill() === '1') {
            $till_id = $this->getRequest()->getParam('till_id');
        } else {
            $till_id = NULL;
        }

        $collection = $this->_getOrderCreditCollection($till_id, $previous_time);
        $arrayAllow = Mage::helper('xpos')->getListDrawerPayment();
        if (count($collection) > 0) {
            foreach ($collection as $creditmemo) {
                $method = $creditmemo->getOrder()->getPayment()->getMethod();
                $amount = floatval($creditmemo->getGrandTotal());
                $data['refund_amount'] += $amount;
                if (in_array($method, $arrayAllow)) {
                    $data['refund_from_cash_drawer'] += $amount;
                }
                $data['order_count']++;
            }
        }
        return $data;
    }

    public function getRewardPointTotal()
    {
        $data = array();
        $collection = Mage::getModel('rewardpoints/rewardpointsorder')->getCollection()->addFieldToFilter('order_id', array('in' => $this->_arrCurrentOrders));
        foreach ($collection as $order) {
            $data['mwrp_amount'] += floatval($order->getData('money'));
            $data['order_count']++;
        }
        return $data;
    }

    public function getGiftCardTotal()
    {
        $data = array();
        $collection = Mage::getModel('giftcards/order')->getCollection()->addFieldToFilter('id_order', array('in' => $this->_arrCurrentOrders));
        foreach ($collection as $order) {
            $a = floatval($order->getData('discounted'));
            $data['gc_amount'] += $a;
            $data['order_count']++;
        }
        return $data;
    }

    private function _getReportCollection($till_id = 0)
    {
        $reportCollection = Mage::getModel('xpos/report')->getCollection()
            ->addFieldToFilter('till_id', array('eq' => $till_id))
            ->addOrder('report_id', 'DESC');
        return $reportCollection;

    }

    private function _getXPosTransacCollection($till_id = 0, $previous_time)
    {
        $xPosTransacCollection = Mage::getModel('xpos/transaction')->getCollection()
            ->addFieldToFilter('till_id', array('eq' => $till_id))
            ->addFieldToFilter('transac_flag', array('eq' => '1'))
            ->addFieldToFilter('created_time', array('from' => $previous_time))
            ->addOrder('transaction_id', 'DESC');
        return $xPosTransacCollection;

    }

    private function _getManualXPosTransacCollection($till_id = 0, $previous_time)
    {
        $xPosTransacCollection =
            Mage::getModel('xpos/transaction')->getCollection()
                ->addFieldToFilter('till_id', array('eq' => $till_id))
                ->addFieldToFilter('order_id', array('eq' => 'Manual'))
                ->addFieldToFilter('transac_flag', array('eq' => '1'))
                ->addFieldToFilter('created_time', array('from' => $previous_time))
                ->addOrder('transaction_id', 'ASC');
        return $xPosTransacCollection;

    }

    private function _getXPosOrderCollection($till_id = null, $previous_time, $baseTotals = true)
    {
        $xPosOrderCollection =
            Mage::getModel('sales/order')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('created_at', array('from' => $previous_time));;

        $xPosOrderCollection->addAttributeToFilter('xpos', array('eq' => 1));
        $this->processData($xPosOrderCollection, $till_id);
        $xPosOrderCollection->load();
        return $xPosOrderCollection;

    }

    private function _getOrderCreditCollection($till_id = null, $previous_time)
    {
        $xPosOrderCollection =
            Mage::getModel('sales/order_creditmemo')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('main_table.updated_at', array('from' => $previous_time))
                ->addAttributeToFilter('p.base_total_paid', array('gt' => 0));
        $xPosOrderCollection->join(
            array('p' => 'sales/order'),
            'main_table.order_id=p.entity_id',
            array(
                'p.xpos'    => 'xpos',
                'p.till_id' => 'till_id',
            )
        );
        $xPosOrderCollection->addAttributeToFilter('xpos', array('eq' => 1));
        $this->processData($xPosOrderCollection, $till_id);
        $xPosOrderCollection->load();
        return $xPosOrderCollection;
    }

    protected function processData($xPosOrderCollection, $till_id)
    {
        $xPosOrderCollection = $this->filterWithTill($xPosOrderCollection, $till_id);
        $xPosOrderCollection = $this->rejectOrderFromXposApi($xPosOrderCollection);
        return $xPosOrderCollection;
    }

    protected function rejectOrderFromXposApi($xPosOrderCollection)
    {
        /*
        * reject order from xposApi
        * */
        if (Mage::getStoreConfig('xposapi/general/enabled')) {
            $xPosOrderCollection->addAttributeToFilter('xpos_app_order_id', array('null' => true));
        }
        return $xPosOrderCollection;
    }

    protected function filterWithTill($xPosOrderCollection, $till_id)
    {
        if (!is_null($till_id)) {
            $xPosOrderCollection->addAttributeToFilter('till_id', array('eq' => $till_id));
        }
        return $xPosOrderCollection;
    }
}
