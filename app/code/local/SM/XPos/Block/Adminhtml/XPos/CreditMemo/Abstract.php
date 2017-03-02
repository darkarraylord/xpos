<?php
/**
 * Created by PhpStorm.
 * User: sonbv
 * Date: 06/08/2015
 * Time: 19:22
 */

class SM_XPos_Block_Adminhtml_XPos_CreditMemo_Abstract extends Mage_Core_Block_Template
{
    /**
     * @var Mage_Sales_Model_Order
     */
    protected $order;

    /**
     * @var Mage_Sales_Model_Order_Creditmemo
     * */
    protected $creditmemo;

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (!isset($this->order)) {
            $this->order = $this->getCreditmemo()->getOrder();
        }
        return $this->order;
    }

    /**
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    public function getCreditmemo()
    {
        if (!isset($this->creditmemo)) {
            $this->creditmemo = Mage::registry('current_creditmemo');
        }
        return $this->creditmemo;
    }


}