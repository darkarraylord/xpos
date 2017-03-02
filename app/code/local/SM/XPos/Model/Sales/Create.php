<?php

class SM_XPos_Model_Sales_Create extends Mage_Adminhtml_Model_Sales_Order_Create {

    protected $_notRepresentOptions = array('info_buyRequest');

    //Overrride init from order function to update price
    public function initFromOrder(Mage_Sales_Model_Order $order) {
        if (!$order->getReordered()) {
            $this->getSession()->setOrderId($order->getId());
        } else {
            $this->getSession()->setReordered($order->getId());
        }

        $this->initXPosDiscount($order);

        /**
         * Check if we edit quest order
         */
        $this->getSession()->setCurrencyId($order->getOrderCurrencyCode());
        if ($order->getCustomerId()) {
            $this->getSession()->setCustomerId($order->getCustomerId());
        } else {
            $this->getSession()->setCustomerId(false);
        }

        $this->getSession()->setStoreId($order->getStoreId());

        //Notify other modules about the session quote
        Mage::dispatchEvent('init_from_order_session_quote_initialized',
            array('session_quote' => $this->getSession()));

        /**
         * Initialize catalog rule data with new session values
         */
        $this->initRuleData();
        foreach ($order->getItemsCollection(
            array_keys(Mage::getConfig()->getNode('adminhtml/sales/order/create/available_product_types')->asArray()),
            true
        ) as $orderItem) {
            /* @var $orderItem Mage_Sales_Model_Order_Item */
            if (!$orderItem->getParentItem()) {
                if ($order->getReordered()) {
                    $qty = $orderItem->getQtyOrdered();
                } else {
                    $qty = $orderItem->getQtyOrdered() - $orderItem->getQtyShipped() - $orderItem->getQtyInvoiced();
                }

                if ($qty > 0) {
                    $item = $this->initFromOrderItem($orderItem, $qty);
                    if (is_string($item)) {
                        Mage::throwException($item);
                    }
                }
            }
        }

        $shippingAddress = $order->getShippingAddress();
        if ($shippingAddress) {
            $addressDiff = array_diff_assoc($shippingAddress->getData(), $order->getBillingAddress()->getData());
            unset($addressDiff['address_type'], $addressDiff['entity_id']);
            $shippingAddress->setSameAsBilling(empty($addressDiff));
        }

        $this->_initBillingAddressFromOrder($order);
        $this->_initShippingAddressFromOrder($order);

        if (!$this->getQuote()->isVirtual() && $this->getShippingAddress()->getSameAsBilling()) {
            $this->setShippingAsBilling(1);
        }

        $this->setShippingMethod($order->getShippingMethod());
        $this->getQuote()->getShippingAddress()->setShippingDescription($order->getShippingDescription());

        $this->getQuote()->getPayment()->addData($order->getPayment()->getData());


        $orderCouponCode = $order->getCouponCode();
        if ($orderCouponCode) {
            $this->getQuote()->setCouponCode($orderCouponCode);
        }

        if ($this->getQuote()->getCouponCode()) {
            $this->getQuote()->collectTotals();
        }

        Mage::helper('core')->copyFieldset(
            'sales_copy_order',
            'to_edit',
            $order,
            $this->getQuote()
        );

        Mage::dispatchEvent('sales_convert_order_to_quote', array(
            'order' => $order,
            'quote' => $this->getQuote()
        ));

        if (!$order->getCustomerId()) {
            $this->getQuote()->setCustomerIsGuest(true);
        }

        if ($this->getSession()->getUseOldShippingMethod(true)) {
            /*
             * if we are making reorder or editing old order
             * we need to show old shipping as preselected
             * so for this we need to collect shipping rates
             */
            $this->collectShippingRates();
        } else {
            /*
             * if we are creating new order then we don't need to collect
             * shipping rates before customer hit appropriate button
             */
            $this->collectRates();
        }

        // Make collect rates when user click "Get shipping methods and rates" in order creating
        // $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
        // $this->getQuote()->getShippingAddress()->collectShippingRates();

        $this->getQuote()->save();
        $cur_quote = $quote = $this->getQuote()->getId();
        $items = Mage::getModel('sales/quote_item')->getCollection()
            ->addFieldToFilter('quote_id', array('eq' => $cur_quote))
            ->getData();
        foreach ($items as $item) {
            if ($item['parent_item_id']) {
                if ($item['custom_price']) {
                    $parent_item = Mage::getModel('sales/quote_item')
                        ->load($item['parent_item_id']);
                    if (($parent_item->getData('product_type') == 'configurable' || $parent_item->getData('product_type') == 'bundle') && is_null($parent_item->getData('custom_price'))) {

                        $data = array();
                        $info = array();
                        $info['qty'] = $parent_item->getData('qty');
                        $info['custom_price'] = $item['custom_price'];
                        $data[$item['parent_item_id']] = $info;
                        $data = $this->processDatas($data);
                        $this->updateQuoteItemsConfig($data);
                    }
                }
            }
        }

        return $this;
    }

    //process
    protected function processDatas($items) {
        /* @var $productHelper Mage_Catalog_Helper_Product */
        $productHelper = Mage::helper('catalog/product');
        foreach ($items as $id => $item) {
            $buyRequest = new Varien_Object($item);
            $params = array('files_prefix' => 'item_' . $id . '_');
            $buyRequest = $productHelper->addParamsToBuyRequest($buyRequest, $params);
            if ($buyRequest->hasData()) {
                $items[$id] = $buyRequest->toArray();
            }
        }
        return $items;
    }

    //Get custom price of save order
    public function initFromOrderItem(Mage_Sales_Model_Order_Item $orderItem, $qty = null) {
        if (!$orderItem->getId()) {
            return $this;
        }
        $info = $orderItem->getProductOptionByCode('info_buyRequest');
        $info = new Varien_Object($info);

        $price = $info->getData('custom_price');
        SM_XPos_Helper_CustomSalesHelper::setFlagCollect();
        $product = Mage::getModel('catalog/product')
            ->setStoreId($this->getSession()->getStoreId())
            ->load($orderItem->getProductId());
        SM_XPos_Helper_CustomSalesHelper::setFlagCollect(false);
        if ($product->getId()) {
            if ($price)
                $product->setData('price', $price);
            $product->setSkipCheckRequiredOption(true);
            $buyRequest = $orderItem->getBuyRequest();
            if (is_numeric($qty)) {
                $buyRequest->setQty($qty);
            }
            // $item = $this->getQuote()->addProduct($product, $buyRequest);
            $item = $this->addProductReload($product, $buyRequest);
            if (is_string($item)) {
                return $item;
            }

            if ($additionalOptions = $orderItem->getProductOptionByCode('additional_options')) {
                $item->addOption(new Varien_Object(
                    array(
                        'product' => $item->getProduct(),
                        'code' => 'additional_options',
                        'value' => serialize($additionalOptions)
                    )
                ));
            }

            Mage::dispatchEvent('sales_convert_order_item_to_quote_item', array(
                'order_item' => $orderItem,
                'quote_item' => $item
            ));
            return $item;
        }

        return $this;
    }

    public function recollectCart() {
        if ($this->_needCollectCart === true) {
            $this->getCustomerCart()
                ->collectTotals()
                ->save();
        }
        $this->setRecollect(true);
        return $this;
    }

    /**
     * Update quantity of order quote items
     *
     * @param   array $data
     * @return  SM_XPos_Model_Adminhtml_Sales_Order_Create
     */
    public function updateQuoteItems($data) {
        if (is_array($data)) {
            try {
                foreach ($data as $itemId => $info) {
                    if (!empty($info['configured'])) {
                        $item = $this->getQuote()->updateItem($itemId, new Varien_Object($info));
                        $itemQty = (float)$item->getQty();
                    } else {
                        $item = $this->getQuote()->getItemById($itemId);
                        $itemQty = (float)$info['qty'];
                    }
                    if ($item) {
                        if ($item->getProduct()->getStockItem()) {
                            if (!$item->getProduct()->getStockItem()->getIsQtyDecimal()) {
                                $itemQty = (int)$itemQty;
                            } else {
                                $item->setIsQtyDecimal(1);
                            }
                        }

                        //$itemQty    = $itemQty > 0 ? $itemQty : 1;
                        if ($itemQty > 0) {
                            if (isset($info['custom_price'])) {
                                $itemPrice = $this->_parseCustomPrice($info['custom_price']);
                            } else {
                                $itemPrice = null;
                            }
                            // $noDiscount = !isset($info['use_discount']);
                            $noDiscount = false;

                            if (empty($info['action']) || !empty($info['configured'])) {
                                $item->setQty($itemQty);
                                $item->setCustomPrice($itemPrice);
                                $item->setOriginalCustomPrice($itemPrice);
                                $item->setNoDiscount($noDiscount);
                                $item->getProduct()->setIsSuperMode(true);
                                $item->getProduct()->unsSkipCheckRequiredOption();
                                $item->checkData();
                            } else {
                                $this->moveQuoteItem($item->getId(), $info['action'], $itemQty);
                            }
                        } else {
                            $this->getQuote()->removeItem($item->getId());
                        }
                    } else {
                        try {
                            $itemQty = (float)$info['qty'];
                            $t = explode('-', $itemId);
                            $realItemId = $itemId;
                            if (isset($t[0])) {
                                $realItemId = $t[0];
                            }

                            if ($itemQty > 0 && $realItemId > 0) {
                                //$reload = Mage::app()->getRequest()->getParam('reload_order');
                                $reload = 0;
                                if ($reload) {
                                    if (strpos($itemId, '-') === false) {
                                        $quote_item = Mage::getModel('sales/quote_item')->load($itemId);
                                        $product_id = $quote_item->getData('product_id');
                                        $this->addProductReload($product_id, $info);
                                    }
                                } else {
                                    SM_XPos_Helper_CustomSalesHelper::setFlagCollect();
                                    $this->addProductReload($itemId, $info);
                                }
                            }
                            /**
                             * Fixed: Cant update custom price at first time click button Update Items
                             */
                            $this->recollectCart();
                            $this->updateCustomPrice($data);
                        } catch (Mage_Core_Exception $e) {
                            $this->getSession()->addError($e->getMessage());
                        } catch (Exception $e) {
                            return $e;
                        }
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $this->recollectCart();
                throw $e;
            } catch (Exception $e) {
                Mage::logException($e);
            }
            $this->recollectCart();
        }
        return $this;
    }


    //update config product
    public function updateQuoteItemsConfig($data) {
        if (is_array($data)) {
            try {
                foreach ($data as $itemId => $info) {
                    $item = $this->getQuote()->getItemById($itemId);
                    $itemQty = (float)$info['qty'];
                    if ($item) {
                        //$itemQty    = $itemQty > 0 ? $itemQty : 1;
                        if ($itemQty > 0) {
                            if (isset($info['custom_price'])) {
                                $itemPrice = $this->_parseCustomPrice($info['custom_price']);
                            } else {
                                $itemPrice = null;
                            }
                            // $noDiscount = !isset($info['use_discount']);
                            $noDiscount = false;

                            if (empty($info['action']) || !empty($info['configured'])) {
                                $item->setQty($itemQty);
                                $item->setCustomPrice($itemPrice);
                                $item->setOriginalCustomPrice($itemPrice);
                                $item->setNoDiscount($noDiscount);
                                $item->getProduct()->setIsSuperMode(true);
                                $item->getProduct()->unsSkipCheckRequiredOption();
                                $item->checkData();

                                $this->recollectCart();
                                $item->save();
                            }
                        } else {
                            $this->getQuote()->removeItem($item->getId());
                        }
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $this->recollectCart();
                throw $e;
            } catch (Exception $e) {
                Mage::logException($e);
            }
            $this->recollectCart();
        }
        return $this;
    }

    public function getItemByProduct($itemId) {
//        $product = Mage::getModel('catalog/product')->load($itemId);
        $product = Mage::getModel('catalog/product')
//            ->setStore($this->_getSession()->getStore())
            ->load($itemId);
        foreach ($this->getQuote()->getAllItems() as $item) {
            if ($item->representProduct($product)) {
                return $item;
            }
        }
        return false;
    }

    private function representProduct($itemId, $info) {
        $product = Mage::getModel('catalog/product')->load($itemId);
        foreach ($this->getQuote()->getAllItems() as $item) {
            $itemProduct = $item->getProduct();
            if (!$product || $itemProduct->getId() != $product->getId()) {
                continue;
            }
            $stickWithinParent = $product->getStickWithinParent();
            if ($stickWithinParent) {
                if ($item->getParentItem() !== $stickWithinParent) {
                    continue;
                }
            }
            $itemOptions = $item->getOptionsByCode();
            $productOptions = $product->getCustomOptions();
            $optionsCompare = unserialize($itemOptions['info_buyRequest']->getValue());
            $flag = true;
            foreach ($optionsCompare as $key => $value) {
                if ($value != $info[$key]) {
                    $flag = false;
                }
            }
            if ($flag == false) {
                continue;
            }
            $s1 = $item->compareOptions($itemOptions, $productOptions);
            $s2 = $item->compareOptions($productOptions, $itemOptions);
            if ($s1 == false && $s2 == false) {
                continue;
            }
            return $item;
        }
        return false;
    }

    public function compareOptions($options1, $options2) {
        foreach ($options1 as $option) {
            $code = $option->getCode();
            if (in_array($code, $this->_notRepresentOptions)) {
                continue;
            }
            if (!isset($options2[$code])
                || ($options2[$code]->getValue() == null)
                || $options2[$code]->getValue() != $option->getValue()
            ) {
                return false;
            }
        }
        return true;
    }

    //Update Custom price

    public function updateCustomPrice($data) {
        if (is_array($data)) {
            try {
                foreach ($data as $itemId => $info) {
                    if (!empty($info['configured']) && !empty($info['bundle'])) {
                        $item = $this->getQuote()->updateItem($itemId, new Varien_Object($info));
                        $itemQty = (float)$item->getQty();
                    } else {
                        /*
                         * Represent Product
                         * Fix: XPOS-2021
                        */
                        $item = $this->representProduct($itemId, $info);
                        $itemQty = (float)$info['qty'];
                    }
                    if ($item) {
                        if ($item->getProduct()->getStockItem()) {
                            if (!$item->getProduct()->getStockItem()->getIsQtyDecimal()) {
                                $itemQty = (int)$itemQty;
                            } else {
                                $item->setIsQtyDecimal(1);
                            }
                        }

                        //$itemQty    = $itemQty > 0 ? $itemQty : 1;
                        if ($itemQty > 0) {
                            if (isset($info['custom_price'])) {
                                $itemPrice = $this->_parseCustomPrice(floatval(preg_replace('/[^\d\.]/', '', $info['custom_price'])));
                            } else {
                                $itemPrice = null;
                            }
                            //HiepHM Fixed bug Can not add Coupon code 9/4/2013
                            // $noDiscount = !isset($info['use_discount']);
                            $noDiscount = false;

                            if (empty($info['action']) || !empty($info['configured'])) {
                                $item->setQty($itemQty);
                                $item->setCustomPrice($itemPrice);
                                $item->setOriginalCustomPrice($itemPrice);
                                $item->setNoDiscount($noDiscount);
                                $item->getProduct()->setIsSuperMode(true);
                                $item->getProduct()->unsSkipCheckRequiredOption();
                                $item->checkData();
                            } else {
                                $this->moveQuoteItem($item->getId(), $info['action'], $itemQty);
                            }
                        }
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $this->recollectCart();
                throw $e;
            } catch (Exception $e) {
                Mage::logException($e);
            }
            $this->recollectCart();
        }
        return $this;
    }

    /**
     * @param $order Mage_Sales_Model_Order
     *
     */
    protected function initXPosDiscount($order) {
        $appliedRuleIds = explode(',', $order->getAppliedRuleIds());
        $discount = $order->getXPosDiscount();
        if ($discount && in_array('XPos', $appliedRuleIds)) {
            Mage::getSingleton('xpos/discount_observer')->setXPosDiscount($discount);
        }
        $this->_processDiscountPerItem($order);
    }

    private function _getItemForUpdate($itemId, $info) {
        $product = Mage::getModel('catalog/product')->load($itemId);
        // Get item
        foreach ($this->getQuote()->getAllItems() as $item) {
            $itemProduct = $item->getProduct();
            if (!$product || $itemProduct->getId() != $product->getId()) {
                continue;
            }
            /**
             * Check maybe product is planned to be a child of some quote item - in this case we limit search
             * only within same parent item
             */
            $stickWithinParent = $product->getStickWithinParent();
            if ($stickWithinParent) {
                if ($item->getParentItem() !== $stickWithinParent) {
                    continue;
                }
            }
            $optionsByCode = $item->getOptionsByCode();
            $optionsCompare = unserialize($optionsByCode['info_buyRequest']->getValue());
            foreach ($optionsCompare as $key => $value) {
                if ($value != $info[$key]) {
                    continue;
                }
            }
            return $item;
        }
        return false;
    }

    public function createOrder() {
        $this->_prepareCustomer();
        $this->_errors = array_unique($this->_errors);

        $quote = $this->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        $shippingMethod = Mage::registry('pos_shipping_method');
        $shippingAddress->setBaseGrandTotal(0);
        $shippingAddress->setGrandTotal(0);
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod($shippingMethod);
        $quote->setTotalsCollectedFlag(false);
        $quote->setSubtotal(0);
        $quote->setBaseSubtotal(0);
        $quote->setSubtotalWithDiscount(0);
        $quote->setBaseSubtotalWithDiscount(0);
        $quote->setGrandTotal(0);
        $quote->setBaseGrandTotal(0);
        $quote->collectTotals();

        $this->_validate();

        $this->_prepareQuoteItems();

        $service = Mage::getModel('xpos/sales_quote', $quote);
        if ($this->getSession()->getOrder()->getId()) {
            $oldOrder = $this->getSession()->getOrder();
            $originalId = $oldOrder->getOriginalIncrementId();
            if (!$originalId) {
                $originalId = $oldOrder->getIncrementId();
            }
            $orderData = array(
                'original_increment_id' => $originalId,
                'relation_parent_id' => $oldOrder->getId(),
                'relation_parent_real_id' => $oldOrder->getIncrementId(),
                'edit_increment' => $oldOrder->getEditIncrement() + 1,
                'increment_id' => $originalId . '-' . ($oldOrder->getEditIncrement() + 1)
            );
            $quote->setReservedOrderId($orderData['increment_id']);
            $service->setOrderData($orderData);
        }

        $order = $service->submit();

        if (!$quote->getCustomer()->getId() || !$quote->getCustomer()->isInStore($this->getSession()->getStore())) {
            $quote->getCustomer()->setCreatedAt($order->getCreatedAt());

            $quote->getCustomer()->save();
        }
        if ($this->getSession()->getOrder()->getId()) {
            $oldOrder = $this->getSession()->getOrder();

            $this->getSession()->getOrder()->setRelationChildId($order->getId());
            $this->getSession()->getOrder()->setRelationChildRealId($order->getIncrementId());
            $this->getSession()->getOrder()->cancel()
                ->save();
            $order->save();
        }
        if ($this->getSendConfirmation()) {
            $order->sendNewOrderEmail();
        }

        Mage::dispatchEvent('checkout_submit_all_after', array('order' => $order, 'quote' => $quote));
        Mage::getModel('xpos/integrate')->giftRegistrySaveOrderAfter($order, $quote);
        return $order;
    }

    public function createOrderForOfflineMode() {

        $this->_prepareCustomer();
        $this->_validate();
        $quote = $this->getQuote();
        $shippingAddress = $quote->getShippingAddress();
        $shippingMethod = Mage::registry('pos_shipping_method');
        if ($shippingMethod) {
            $shippingAddress->setCollectShippingRates(true)->collectShippingRates()
                ->setShippingMethod($shippingMethod);
        }

        $this->_prepareQuoteItems();

        $service = Mage::getModel('sales/service_quote', $quote);
        if ($this->getSession()->getOrder()->getId()) {
            $oldOrder = $this->getSession()->getOrder();
            $originalId = $oldOrder->getOriginalIncrementId();
            if (!$originalId) {
                $originalId = $oldOrder->getIncrementId();
            }
            $orderData = array(
                'original_increment_id' => $originalId,
                'relation_parent_id' => $oldOrder->getId(),
                'relation_parent_real_id' => $oldOrder->getIncrementId(),
                'edit_increment' => $oldOrder->getEditIncrement() + 1,
                'increment_id' => $originalId . '-' . ($oldOrder->getEditIncrement() + 1)
            );
            $quote->setReservedOrderId($orderData['increment_id']);
            $service->setOrderData($orderData);
        }

        $order = $service->submit();
        if ((!$quote->getCustomer()->getId() || !$quote->getCustomer()->isInStore($this->getSession()->getStore()))
            && !$quote->getCustomerIsGuest()
        ) {
            $quote->getCustomer()->setCreatedAt($order->getCreatedAt());
            $quote->getCustomer()
                ->save()
                ->sendNewAccountEmail('registered', '', $quote->getStoreId());;
        }
        if ($this->getSession()->getOrder()->getId()) {
            $oldOrder = $this->getSession()->getOrder();

            $this->getSession()->getOrder()->setRelationChildId($order->getId());
            $this->getSession()->getOrder()->setRelationChildRealId($order->getIncrementId());
            $this->getSession()->getOrder()->cancel()
                ->save();
            $order->save();
        }
        if ($this->getSendConfirmation()) {
            $order->sendNewOrderEmail();
        }

        Mage::dispatchEvent('checkout_submit_all_after', array('order' => $order, 'quote' => $quote));

        return $order;
    }

    public function getCustomerGroupId() {
        $orderXpos = Mage::registry('xpos_order');
        if (isset($orderXpos))
            $groupId = $orderXpos;
        else
            $groupId = $this->getQuote()->getCustomerGroupId();
        if (!$groupId) {
            $groupId = $this->getSession()->getCustomerGroupId();
        }
        return $groupId;
    }

    public function addProduct($product, $config = 1) {
        if (!is_array($config) && !($config instanceof Varien_Object)) {
            $config = array('qty' => $config);
        }
        $config = new Varien_Object($config);

        if (!($product instanceof Mage_Catalog_Model_Product)) {
            $productId = $product;
            SM_XPos_Helper_CustomSalesHelper::setFlagCollect();
            $product = Mage::getModel('catalog/product')
                ->setStore($this->getSession()->getStore())
                ->setStoreId($this->getSession()->getStoreId())
                ->load($product);
            SM_XPos_Helper_CustomSalesHelper::setFlagCollect(false);
            if (!$product->getId()) {
                Mage::throwException(
                    Mage::helper('adminhtml')->__('Failed to add a product to cart by id "%s".', $productId)
                );
            }
        }

        $stockItem = $product->getStockItem();
        if ($stockItem && $stockItem->getIsQtyDecimal()) {
            $product->setIsQtyDecimal(1);
        } else {
            $config->setQty((int)$config->getQty());
        }

        $product->setCartQty($config->getQty());
        $item = $this->getQuote()->addProductAdvanced(
            $product,
            $config,
            Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_FULL
        );
        if (is_string($item)) {
            $item = $this->getQuote()->addProductAdvanced(
                $product,
                $config,
                Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_LITE
            );
            if (is_string($item)) {
                Mage::throwException($item);
            }
        }
        $item->checkData();

        $this->setRecollect(true);
        return $this;
    }

    public function addProductReload($product, $config = 1) {
        if (!($config instanceof Varien_Object)) {
            if (!is_array($config)) {
                $config = array('qty' => $config);
            }
            $config = new Varien_Object($config);
        }

        if (!($product instanceof Mage_Catalog_Model_Product)) {
            $productId = $product;
            $product = Mage::getModel('catalog/product')
                ->setStore($this->getSession()->getStore())
                ->setStoreId($this->getSession()->getStoreId())
                ->load($product);
            SM_XPos_Helper_CustomSalesHelper::setFlagCollect(false);
            if (!$product->getId()) {
                Mage::throwException(
                    Mage::helper('adminhtml')->__('Failed to add a product to cart by id "%s".', $productId)
                );
            }
        }

        $stockItem = $product->getStockItem();
        if ($stockItem && $stockItem->getIsQtyDecimal()) {
            $product->setIsQtyDecimal(1);
        } else {
            $config->setQty((int)$config->getQty());
        }

        $product->setCartQty($config->getQty());
        $item = $this->getQuote()->addProductAdvanced(
            $product,
            $config,
            Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_FULL
        );
        if (is_string($item)) {
            $item = $this->getQuote()->addProductAdvanced(
                $product,
                $config,
                Mage_Catalog_Model_Product_Type_Abstract::PROCESS_MODE_LITE
            );
            if (is_string($item)) {
                Mage::throwException($item);
            }
        }

        $itemPrice = -1;
        if (isset($config['custom_price'])) {
            $itemPrice = $this->_parseCustomPrice($config['custom_price']);
        }

        //XPOS-1447: If grouped product then do not process further since their children already be added in addProductAdvanced()
        if (isset($config['super_group'])) {
            return $this;
        }

        $itemQty = (float)$config['qty'];
        if (empty($config['action']) || !empty($config['configured'])) {
            if ($itemPrice != -1) {
                $item->setQty($itemQty);
                $item->setCustomPrice($itemPrice);
                $item->setOriginalCustomPrice($itemPrice);
                $item->getProduct()->setIsSuperMode(true);
                $item->getProduct()->unsSkipCheckRequiredOption();
            }
            //$item->checkData();
        }
        $item->checkData();

        $this->setRecollect(true);
        return $item;
    }

    protected function _validate() {
        $customerId = $this->getSession()->getCustomerId();
        if (is_null($customerId)) {
            Mage::throwException(Mage::helper('adminhtml')->__('Please select a customer.'));
            Mage::log('Not have customer to validate', null, 'vjcspy_XPos_Model_Sales_Create.txt', true);
        }

        if (!$this->getSession()->getStore()->getId()) {
            Mage::throwException(Mage::helper('adminhtml')->__('Please select a store.'));
            Mage::log('Not have Store to validate', null, 'vjcspy_XPos_Model_Sales_Create.txt', true);
        }
        $items = $this->getQuote()->getAllItems();

        if (count($items) == 0) {
            $this->_errors[] = Mage::helper('adminhtml')->__('You need to specify order items.');
            Mage::log('Not have item to validate', null, 'vjcspy_XPos_Model_Sales_Create.txt', true);
        }

        foreach ($items as $item) {
            $messages = $item->getMessage(false);
            if ($item->getHasError() && is_array($messages) && !empty($messages)) {
                $this->_errors = array_merge($this->_errors, $messages);
            }
        }

        if (!$this->getQuote()->isVirtual()) {
            if (!$this->getQuote()->getShippingAddress()->getShippingMethod()) {
                $this->_errors[] = Mage::helper('adminhtml')->__('Shipping method must be specified.');
                Mage::log('Not have Shipping method to validate', null, 'vjcspy_XPos_Model_Sales_Create.txt', true);
            }
        }

        if (!$this->getQuote()->getPayment()->getMethod()) {
            $this->_errors[] = Mage::helper('adminhtml')->__('Payment method must be specified.');
            Mage::log('Not have payment method to validate', null, 'vjcspy_XPos_Model_Sales_Create.txt', true);
        } else {
            $method = $this->getQuote()->getPayment()->getMethodInstance();
            if (!$method) {
                $this->_errors[] = Mage::helper('adminhtml')->__('Payment method instance is not available.');
                Mage::log('Payment method not available to validate', null, 'vjcspy_XPos_Model_Sales_Create.txt', true);
            } else {
                if (!$method->isAvailable($this->getQuote())) {
                    $this->_errors[] = Mage::helper('adminhtml')->__('Payment method is not available.');
                    Mage::log('Payment method not available to validate', null, 'vjcspy_XPos_Model_Sales_Create.txt', true);
                } else {
                    try {
                        $method->validate();
                    } catch (Mage_Core_Exception $e) {
                        $this->_errors[] = $e->getMessage();
                    }
                }
            }
        }

        if (!empty($this->_errors)) {
            foreach ($this->_errors as $error) {
                $this->getSession()->addError($error);
            }
            Mage::throwException('');
        }
        return $this;
    }

    /*
     * init Discount per item
     * */
    protected function _processDiscountPerItem($order) {
        if($order->getData('discount_amount') == 0){
            return;
        };
        $items = $order->getAllVisibleItems();
        $totalDiscount = 0;
        $discountData = array();
        foreach ($items as $item) {
            /*
             *  if is custom sale then reject
             * */
            if( $item['sku'] == 'custom_sales_product_for_xpos'){
                continue;
            }
            if ( $item['product_type'] == 'bundle') {
                continue;
            }
            $option = $item->getData('product_options');
            $child_product_id = null;
            if(!empty($option)){
                $infoBuyRequest = unserialize($option);
                $infoBuyRequest = $infoBuyRequest['info_buyRequest'];
                /** @var Mage_Catalog_Model_Product_Type_Configurable $productTypeInstance */
                $productTypeInstance = $item->getProduct()->getTypeInstance();

                if ( $item['product_type'] != 'simple') {
                    if ($item['product_type'] == Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE) {
                        $child_product_id = $productTypeInstance->getProduct()->getId();
                    }
                    if($item['product_type'] == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE){
                        $childData = isset($infoBuyRequest['super_attribute'])?$infoBuyRequest['super_attribute']:array();
                        $productTypeInstance->getProductByAttributes($childData) ;
                        $child_product_id = $productTypeInstance->getProductByAttributes($childData)->getId();
                    }
                }
            }
            $itemId = $item['product_id'];
            if(!is_null($child_product_id)){
                $itemId = $child_product_id;
            }
            $productId = $item['product_id'];
            $qty = floatval($item['qty_ordered']);
            $discount = floatval($item['discount_amount']);
            $baseDiscount = floatval($item['base_discount_amount']);
            if ($baseDiscount) {
                $totalDiscount += $baseDiscount;
                $itemData['discount'] = $baseDiscount;
                $itemData['product_id'] = $itemId;
                $itemData['qty'] = $qty;
                $discountData[] = $itemData;
            }
        }
        if ($totalDiscount > 0) {
            Mage::getSingleton('xpos/discount_item_collection')->setXposDiscountPerItem($discountData);
        }
    }

}
