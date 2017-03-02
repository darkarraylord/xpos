<?php

class SM_XPayment_Block_Info_XpaymentMultiple extends Mage_Payment_Block_Info
{
    protected $_emi;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('xpayment/info/xpaymentMultiple.phtml');
    }
    public function getConfigDataPaymentMethod($code, $field) {
        $path = 'payment/' . $code . '/' . $field;

        return Mage::getStoreConfig($path);
    }
    public function getEmi()
    {
        if (is_null($this->_emi)) {
            $this->_convertAdditionalData();
        }
        return $this->_emi;
    }

    protected function _convertAdditionalData()
    {
        $this->_emi = null;

        $details = @unserialize($this->getInfo()->getAdditionalData());
        if($details != null){
            $this->_emi = $details;
        }
        return $this;
    }
}
