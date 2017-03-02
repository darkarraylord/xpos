<?php

class SM_XPos_Model_Transaction extends Mage_Core_Model_Abstract{
    public function _construct(){
        parent::_construct();
        $this->_init('xpos/transaction');
    }

    public function getType(){
        return $this->getData('type');
    }

    public function getOrderId(){
        return $this->getData('order_id');
    }

    public function currentBalance($till_id)
    {
        //NamLX calculator by till
        if (!$till_id) {
            $till_id = Mage::getSingleton('adminhtml/session')->getTillId();
        }
        if ($till_id == NULL && $till_id == '') {
            $till_id = 0;
        }
        $collection = Mage::getModel('xpos/transaction')
            ->getCollection()
            ->addFieldToSelect('current_balance')
            ->addFieldToFilter('till_id', $till_id)
            ->setOrder('transaction_id', 'DESC')
            ->setPageSize(1);
        $current_balance = $collection->getFirstItem()->getData('current_balance');
        /*
        if($till_id != NULL && $till_id != ''){
            $collection = Mage::getSingleton('xpos/transaction')->getCollection()
                        ->addFieldToFilter('till_id', array('eq' => $till_id))
                        ->addFieldToFilter('comment', array('neq' => 'Out payment'))
                        ->load();
            $cash_in = 0;
            $cash_out = 0;
            foreach($collection as $row){
                $cash_in += $row->getData('cash_in');
                $cash_out += $row->getData('cash_out');
            }
            $current_balance  = $cash_in - $cash_out;
        }
        else
            $current_balance = $readConnection->fetchOne('SELECT current_balance FROM '.$catalogResource->getTable('sm_transaction').' WHERE 1 ORDER BY transaction_id DESC LIMIT 1');
        */
        //$current_balance = $readConnection->fetchOne('SELECT current_balance FROM '.$catalogResource->getTable('sm_transaction').' WHERE 1 ORDER BY transaction_id DESC LIMIT 1');
        $_store = Mage::app()->getStore(Mage::helper('xpos')->getXPosStoreId());
        $current_balance = $_store->convertPrice($current_balance, true, false);
        $return = array();
        $return['msg'] = $current_balance;
        return $return;
    }

    /*
     * Save transaction
     */
    public function saveTransaction($data){

        $return = array(
            'msg'=>'Error! Please recheck the form OR contact administrator for more details.',
            'error'=>true);

        $user_id = 0;
        if(Mage::getSingleton('admin/session')->getUser()){
            $user_id = Mage::getSingleton('admin/session')->getUser()->getId();
        }

        $till_id = $data['till_id'];
        if ($till_id == "" || $till_id == NULL) {
            $till_id = 0;
        }
        $collection = Mage::getModel('xpos/transaction')
            ->getCollection()
            ->addFieldToSelect('current_balance')
            ->addFieldToFilter('till_id', $till_id)
            ->setOrder('transaction_id', 'DESC')
            ->setPageSize(1);
        $current_balance = $collection->getFirstItem()->getData('current_balance');

        $arrayAllow = Mage::helper('xpos')->getListDrawerPayment();
        $now = date('Y-m-d H:i:s');
        switch ($data['type']) {
            case 'in':
                $previous_balance = $current_balance;
                $current_balance += $data['amount'];
                if (!isset($data['xpos_user_id']))
                    $data['xpos_user_id'] = NULL;
                if (!isset($data['warehouse_id']))
                    $data['warehouse_id'] = NULL;
                $return = $this->_saveData(
                    $data['amount'],
                    0,
                    'in',
                    $now,
                    'Manual',
                    $previous_balance,
                    $current_balance,
                    $user_id,
                    $data['xpos_user_id'],
                    'cash_in',
                    $data['note'],
                    $data['warehouse_id'],
                    $data['till_id']
                );
                break;

            case 'out':

                if ($current_balance >= $data['amount']) {
                    $previous_balance = $current_balance;
                    $current_balance -= $data['amount'];
                    if (!isset($data['xpos_user_id']))
                        $data['xpos_user_id'] = NULL;
                    if (!isset($data['warehouse_id']))
                        $data['warehouse_id'] = NULL;
                    $return = $this->_saveData(
                        0,
                        $data['amount'],
                        'out',
                        $now,
                        'Manual',
                        $previous_balance,
                        $current_balance,
                        $user_id,
                        $data['xpos_user_id'],
                        'cash_out',
                        $data['note'],
                        $data['warehouse_id'],
                        $data['till_id']
                    );
                } else {
                    $return['msg'] = 'You can NOT withdraw an amount of money which is greater than the Current Balance';
                    $return['error'] = true;
                }

                break;
            case 'refund':
                if ($current_balance >= $data['amount']) {
                    $previous_balance = $current_balance;
                    $current_balance -= $data['amount'];
                    if (empty($data['xpos_user_id']))
                        $data['xpos_user_id'] = NULL;
                    if (empty($data['warehouse_id']))
                        $data['warehouse_id'] = NULL;
                    $return = $this->_saveData(
                        0,
                        $data['amount'],
                        'out',
                        $now,
                        'Refund',
                        $previous_balance,
                        $current_balance,
                        $user_id,
                        $data['xpos_user_id'],
                        $data['method'],
                        'Order '.$data['order_id'],
                        $data['warehouse_id'],
                        $data['till_id']
                    );
                } else {
                    $return['msg'] = 'You can NOT withdraw an amount of money which is greater than the Current Balance';
                    $return['error'] = true;
                }
                break;
            default:
                if (in_array($data['type'], $arrayAllow)) {
                    $amount = $data['cash_in'] - $data['cash_out'];
                    $previous_balance = $current_balance;
                    $note = $data['payment_method'];
                    $current_balance += $amount;
                    $return = $this->_saveData(
                        $data['cash_in'],
                        $data['cash_out'],
                        'in',
                        $now,
                        $data['order_id'],
                        $previous_balance,
                        $current_balance,
                        $user_id,
                        $data['xpos_user_id'],
                        $data['payment_method'],
                        $note,
                        $data['warehouse_id'],
                        $data['till_id']
                    );
                }
                break;
        }


        return $return;

    }


    /**
     * Read report
     */

    public function reportTransaction($data)
    {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $tableName = 'sm_pos_transaction';
        $query = 'SELECT * FROM ';
        $results = $readConnection->fetchAll($query);

    }

    private function _saveData(
        $cashIn, $cashOut, $type, $time, $orderId, $previousBalance, $currentBalance,
        $userId, $xposUserId, $payment, $note, $wareHouseId, $tillId
    )
    {
        try {

            $this->setData(
                array(
                    'cash_in'          => $cashIn,
                    'cash_out'         => $cashOut,
                    'type'             => $type,
                    'created_time'     => $time,
                    'order_id'         => $orderId,
                    'previous_balance' => $previousBalance,
                    'current_balance'  => $currentBalance,
                    'user_id'          => $userId,
                    'xpos_user_id'     => $xposUserId,
                    'payment_method'   => $payment,
                    'comment'          => $note,
                    'warehouse_id'     => $wareHouseId,
                    'till_id'          => $tillId,
                    'transac_flag'     => 1,
                )
            );
            $this->save();
            $return['msg'] = 'Transaction saved';
            $return['error'] = false;
        } catch (Mage_Core_Exception $e) {
            $return['msg'] = 'Can NOT save this transaction';
            $return['error'] = true;
        }
        return $return;
    }

}