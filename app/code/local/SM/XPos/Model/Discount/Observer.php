<?php

/**
 * Created by PhpStorm.
 * User: sonbv
 * Date: 17/06/2015
 * Time: 15:12
 */
class SM_XPos_Model_Discount_Observer extends Mage_Core_Model_Abstract
{

    const XPOS_DISCOUNT_SESSION_KEY = 'xpos_discount';
    const XPOS_OFFLINE_DISCOUNT_SESSION_KEY = 'discount_hidden';
    const  XPOS_OFFLINE_ORDER = 'xpos_offline_order';

    const XPOS_DISCOUNT_QUOTE_ID_SESSION_KEY = 'xpos_discount_quote_id';

    public function coreCollectionAbstractLoadBefore($event)
    {


        $collection = $event->getCollection();
        /** @var $collection Mage_SalesRule_Model_Resource_Rule_Collection */
        if ($collection instanceof Mage_SalesRule_Model_Resource_Rule_Collection) {
            $module = Mage::app()->getRequest()->getControllerModule();
            if ($module !== 'SM_XPos_Adminhtml') {
                return;
            }

            $discount = $this->getXPosDiscount();

            if ($discount
                && $this->getCurrentQuote()->getId()
                && $this->getCurrentQuote()->getId() === $this->getSession()->getData(self::XPOS_DISCOUNT_QUOTE_ID_SESSION_KEY)
            ) {
                $rule = SM_XPos_Model_Discount_Rule::getRule('fixed');
                $rule->setDiscountAmount($discount);
                $collection->addItem($rule);
            }

            $rules = Mage::getSingleton('xpos/discount_item_collection')->getRules();
            foreach($rules as $rule) {
                $collection->addItem($rule);
            }
        }
    }

    public function getQuoteXPosDiscount()
    {
        $discount = $this->getXPosDiscount();

        if ($discount
            && $this->getCurrentQuote()->getId()
            && $this->getCurrentQuote()->getId() === $this->getSession()->getData(self::XPOS_DISCOUNT_QUOTE_ID_SESSION_KEY)
        ) {
            return $discount;
        }
        return 0;
    }


    protected function getXPosDiscount()
    {
        if(!!$this->getSession()->getData(self::XPOS_OFFLINE_ORDER)){
            $discount = Mage::app()->getRequest()->getPost(self::XPOS_OFFLINE_DISCOUNT_SESSION_KEY);
            $this->getSession()->setData(self::XPOS_DISCOUNT_SESSION_KEY, $discount);
            $this->getSession()->setData(self::XPOS_DISCOUNT_QUOTE_ID_SESSION_KEY, $this->getCurrentQuote()->getId());
            return $this->getSession()->getData(self::XPOS_DISCOUNT_SESSION_KEY);
        }
        $discount = Mage::app()->getRequest()->getPost(self::XPOS_DISCOUNT_SESSION_KEY);
        if (!is_null($discount)) {
            $discount = abs($this->getBaseDiscountValue(floatval($discount)));
            if ($discount > 0) {
                $this->setXPosDiscount($discount);
            } else {
                $this->getSession()->unsetData(self::XPOS_DISCOUNT_SESSION_KEY);
            }
        }

        return $this->getSession()->getData(self::XPOS_DISCOUNT_SESSION_KEY);
    }

    protected function getCurrentQuote()
    {
        return Mage::getSingleton('xpos/sales_create')->getQuote();
    }

    protected function getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * @param $discount
     */
    public function setXPosDiscount($discount)
    {
        $this->getSession()->setData(self::XPOS_DISCOUNT_SESSION_KEY, $discount);
        $this->getSession()->setData(self::XPOS_DISCOUNT_QUOTE_ID_SESSION_KEY, $this->getCurrentQuote()->getId());
    }

    public function xPosCompleteOrderBefore($event)
    {
        $discount = $this->getXPosDiscount();

        if ($discount
            && $this->getCurrentQuote()->getId()
            && $this->getCurrentQuote()->getId() === $this->getSession()->getData(self::XPOS_DISCOUNT_QUOTE_ID_SESSION_KEY)
        ) {
            $event->getOrder()->setXPosDiscount($discount);
        }
    }

    protected function getBaseDiscountValue($discount)
    {
        /** @var Mage_Core_Model_Store $store */
        $store = $this->getCurrentQuote()->getStore();

        if ($store->getCurrentCurrency() && $store->getBaseCurrency()) {
            $rate = $store->getBaseCurrency()->getRate($store->getCurrentCurrency());
            if ($rate) {
                return $discount / $rate;
            }

            throw new Exception(Mage::helper('directory')->__('Undefined rate from "%s-%s".', $store->getBaseCurrency(),
                $store->getCurrentCurrency()->getCode()));
        }

        return $discount;
    }

}
