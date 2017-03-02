<?php

/**
 * Created by PhpStorm.
 * User: SMART
 * Date: 12/23/2015
 * Time: 6:10 PM
 */
class SM_XPos_Model_Discount_Item_Collection
{
    const XPOS_DISCOUNT_ITEM_SESSION_KEY = 'xpos_discount_item';

    const XPOS_DISCOUNT_ITEM_QUOTE_ID_SESSION_KEY = 'xpos_discount_item_quote_id';

    protected $_items = array();

    protected $_rules;

    public function __construct()
    {
        $this->_initItems();
    }

    public function getRules()
    {
        if (!isset($this->_rules)) {
            $this->_rules = array();
            foreach ($this->_items as $item) {
                $rule = SM_XPos_Model_Discount_Rule::getRule('item_fixed');
                $rule->dummyData();
                $rule->setDiscountAmount($item['discount'] / $item['qty']);
                $rule->setItemData($item);
                $this->_rules[] = $rule;
            }
        }
        return $this->_rules;
    }


    protected function _initItems()
    {
        $items = Mage::app()->getRequest()->getPost('item');
//        var_dump($items);die;
        if (!is_null($items)) {
            $this->_removeSession();
            $helper = Mage::helper('xpos');
            $storeId = $this->getCurrentQuote()->getStore()->getId();
            foreach ($items as $id => $item) {
                if (!isset($item['discount']) || $item['discount'] < 0) continue;
                if (!isset($item['qty']) || $item['qty'] <= 0) continue;
                $discount = $helper->getConvertedValue($storeId, floatval($item['discount']));
                if (($pos = strpos($id, '-')) === false) {
                    $itemId = $item['pId'];
                    $product = Mage::getModel('catalog/product')->load($itemId);
//                    $item['item_id'] = $id;
                    if($product->getTypeId() == 'bundle'){
                        continue;
                    }else{
                        $item['product_id'] = $item['pId'];
                        $item['qty'] = floatval($item['qty']);
                        $item['discount'] = $discount;
                        $check = $this->checkProduct($item['pId']);
                        if (is_bool($check) && $check == true) {
                            $this->_items[] = $item;
                        } else {
//                        $this->_items[$check]['discount'] += $discount;
                        }
                    }
                } else {
                    $itemId = substr($id, 0, $pos);
                    $product = Mage::getModel('catalog/product')->load($itemId);
                    if ($product->getTypeId() == 'grouped') {
                        $count = count($item['super_group']);
                        foreach ($item['super_group'] as $id => $qty) {
                            $childItem = array();
                            $childItem['discount'] = $discount / $count;
                            $childItem['product_id'] = $id;
                            $childItem['qty'] = floatval($qty);
                            $check = $this->checkProduct($id);
                            if (is_bool($check) && $check == true) {
                                $this->_items[] = $childItem;
                            } else {
//                                $this->_items[$check]['discount'] +=  $childItem['discount'];
                            }
                        }

                    }else {
                        $item['product_id'] = $item['pId'];
                        $item['qty'] = floatval($item['qty']);
                        $item['discount'] = $discount;
                        $check = $this->checkProduct($item['pId']);
                        if (is_bool($check) && $check == true) {
                            $this->_items[] = $item;
                        } else {
//                            $this->_items[$check]['discount'] += $discount;
                        }
                    }
                }
            }
            $this->_saveItemsToSession();
        } else {
            $this->_initItemsFromSession();
        }
    }

    protected function _saveItemsToSession()
    {
        if (empty($this->_items)) return;
        $this->getSession()->setData(self::XPOS_DISCOUNT_ITEM_SESSION_KEY, $this->_items);
        $this->getSession()->setData(self::XPOS_DISCOUNT_ITEM_QUOTE_ID_SESSION_KEY, $this->getCurrentQuote()->getId());
    }

    protected function _removeSession()
    {
        $this->getSession()->setData(self::XPOS_DISCOUNT_ITEM_SESSION_KEY);
    }

    protected function _initItemsFromSession()
    {
        $quoteId = $this->getSession()->getData(self::XPOS_DISCOUNT_ITEM_QUOTE_ID_SESSION_KEY);
        $items = $this->getSession()->getData(self::XPOS_DISCOUNT_ITEM_SESSION_KEY);
        if (!!$quoteId && !!$items && $quoteId === $this->getCurrentQuote()->getId()) {
            $this->_items = $items;
        }
    }

    /*
     * set discount data when reload
     * */
    public function setXposDiscountPerItem($data)
    {
        $this->_items = $data;
        $this->getSession()->setData(self::XPOS_DISCOUNT_ITEM_SESSION_KEY, $this->_items);
        $this->getSession()->setData(self::XPOS_DISCOUNT_ITEM_QUOTE_ID_SESSION_KEY, $this->getCurrentQuote()->getId());
    }

    /**
     * @return Mage_Adminhtml_Model_Session
     */
    protected function getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    /**
     * @return Mage_Sales_Model_Quote
     */
    protected function getCurrentQuote()
    {
        return Mage::getSingleton('xpos/sales_create')->getQuote();
    }

    /**
     * @return array
     */
    public function getItems()
    {
        $items = array();
        $storeId = $this->getCurrentQuote()->getStore()->getId();
        foreach ($this->_items as $key => $item) {
            $itemData = array();
            foreach ($item as $k => $value) {
                if ($k == 'discount') {
                    $item[$k] = $helper = Mage::helper('xpos')->getUnConvertedValue($storeId, $item['discount']);
                }
                $itemData[$k] = $item[$k];
            }
            $items[] = $itemData;
        }

        return $items;
    }

    protected function checkProduct($productId)
    {
        foreach ($this->_items as $k => $item) {
            if ($item['product_id'] == $productId) {
                return $k;
            }
        }
        return true;
    }
}