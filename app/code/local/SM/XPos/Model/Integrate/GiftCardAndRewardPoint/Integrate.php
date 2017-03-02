<?php

/**
 * Created by PhpStorm.
 * User: vjcspy
 * Date: 8/28/15
 * Time: 11:02 AM
 */
class SM_XPos_Model_Integrate_GiftCardAndRewardPoint_Integrate extends Mage_Core_Model_Abstract {


    /*TODO: Integrate with MageStoreRewardPoint*/
    public function isIntegrateRp() {
        if (!!Mage::getStoreConfig('rewardpoints/general/enable'))
            if (Mage::getStoreConfig('xpos/integrates/reward_points_integrate') == 'MageStore')
                return true;
            else
                return false;
        else
            return false;
    }

    public function setSessionCustomer($customerId) {
        Mage::getSingleton('customer/session')->setCustomerId($customerId);
    }

    public $_rewardAccount = null;

    public function getBalanceAccountFormated($customerId) {
        $balance = $this->getBalanceByCustomerId($customerId);

        return Mage::helper('rewardpoints/point')->format(
            $balance, Mage::helper('xpos/product')->getCurrentSessionStoreId()
        );
    }

    public function getBalanceByCustomerId($customerId) {
        if (is_null($this->_rewardAccount)) {
            $this->_rewardAccount = Mage::getModel('rewardpoints/customer');
            $this->_rewardAccount->load($customerId, 'customer_id');
            $this->_rewardAccount->setData('customer', $customerId);
        }

        return $balance = $this->_rewardAccount->getPointBalance();
    }

    public function getPointMoneyByCustomerId($customerId) {
        $pointAmount = $this->getBalanceByCustomerId($customerId);
        if ($pointAmount > 0) {
            $rate = $this->getRate(Magestore_RewardPoints_Model_Rate::POINT_TO_MONEY);
            if ($rate && $rate->getId()) {
                $baseAmount = $pointAmount * $rate->getMoney() / $rate->getPoints();

                return Mage::app()->getStore()->convertPrice($baseAmount, true);
            }
        }

        return '';
    }

    public function getPointMoneyByCustomerIdDefault($cutomerId) {
        $pointAmount = $this->getBalanceByCustomerId($cutomerId);
        $customerGroupId = Mage::getModel('customer/customer')->load($cutomerId)->getGroupId();
        $websiteId = Mage::getModel('core/store')->load(Mage::helper('xpos/product')->getCurrentSessionStoreId())->getWebsiteId();

        if ($pointAmount > 0) {
            $rate = Mage::getModel('rewardpoints/rate')->getRate(Magestore_RewardPoints_Model_Rate::POINT_TO_MONEY, $customerGroupId, $websiteId);
            if ($rate && $rate->getId()) {
                $baseAmount = $pointAmount * $rate->getMoney() / $rate->getPoints();

                return Mage::app()->getStore()->convertPrice($baseAmount, true, false);
            }
        }

        return '';
    }

    public function getRate($direction = 1, $customerGroupId = null, $websiteId = null) {
        if (is_null($customerGroupId)) {
            $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
        }
        if (is_null($websiteId)) {
            $websiteId = Mage::getModel('core/store')->load(Mage::helper('xpos/product')->getCurrentSessionStoreId())->getWebsiteId();
        }
        $rateCollection = Mage::getModel('rewardpoints/rate')->getCollection()
            ->addFieldToFilter('direction', $direction)
            ->addFieldToFilter('website_ids', array('finset' => $websiteId))
            ->addFieldToFilter('customer_group_ids', array('finset' => $customerGroupId))
            ->addFieldToFilter('points', array('gt' => 0))
            ->addFieldToFilter('status', array('eq' => self::STATUS_ACTIVE))
            ->addFieldToFilter('money', array('gt' => 0));
        $rateCollection->getSelect()->order('sort_order DESC');
        $rateCollection->getSelect()->order('rate_id DESC');
        $rate = $rateCollection->getFirstItem();
        if ($rate && $rate->getId()) {
            return $rate;
        }

        return false;
    }


    /*TODO: Integrate with Webtex GiftCard*/

    public function isIntegrateWithWebtexGiftCard() {
        if (Mage::getStoreConfig('xpos/integrates/giftt_card_integrate') == SM_XPos_Model_Adminhtml_System_Config_SourceModel_GiftCardIntegrate::$WEBTEX_GIFTCARD)
            return true;
        else
            return false;
    }

    public function activeGiftCards($giftCardCode) {
        $card = Mage::getModel('giftcards/giftcards')->load($giftCardCode, 'card_code');

        if ($card->getId() && ($card->getCardStatus() == 1)) {
            $card->activateCard();
            Mage::getSingleton('giftcards/session')->setActive('1');
            $this->_setSessionVars($card);
            $this->_getSession()->addSuccess('Gift Card "' . Mage::helper('core')->escapeHtml($giftCardCode) . '" was applied.', Mage::helper('core')->escapeHtml($giftCardCode)
            );
        } else {
            if ($card->getId() && ($card->getCardStatus() == 2)) {
                $this->_getSession()->addError('Gift Card "' . Mage::helper('core')->escapeHtml($giftCardCode) . '" was used.'
                );
            } else {
                $this->_getSession()->addError('Gift Card "' . Mage::helper('core')->escapeHtml($giftCardCode) . '" is not valid.', Mage::helper('core')->escapeHtml($giftCardCode)
                );
            }
        }
    }

    private function _setSessionVars($card) {
        $oSession = Mage::getSingleton('giftcards/session');

        $giftCardsIds = $oSession->getGiftCardsIds();

        //append applied gift card id to gift card session
        //append applied gift card balance to gift card session
        if (!empty($giftCardsIds)) {
            $giftCardsIds = $oSession->getGiftCardsIds();
            if (!array_key_exists($card->getId(), $giftCardsIds)) {
                $giftCardsIds[$card->getId()] = array('balance' => $card->getCardBalance(), 'code' => substr($card->getCardCode(), -4));
                $oSession->setGiftCardsIds($giftCardsIds);

                $newBalance = $oSession->getGiftCardBalance() + $card->getCardBalance();
                $oSession->setGiftCardBalance($newBalance);
            }
        } else {
            $giftCardsIds[$card->getId()] = array('balance' => $card->getCardBalance(), 'code' => substr($card->getCardCode(), -4));
            $oSession->setGiftCardsIds($giftCardsIds);

            $oSession->setGiftCardBalance($card->getCardBalance());
        }
    }

    protected function _getSession() {
        return Mage::getSingleton('adminhtml/session_quote');
    }

    public function deActiveGiftCard($id) {
        $oSession = Mage::getSingleton('giftcards/session');
        $cardId = $id;
        $cardIds = $oSession->getGiftCardsIds();
        $sessionBalance = $oSession->getGiftCardBalance();
        $newSessionBalance = $sessionBalance - $cardIds[$cardId]['balance'];
        unset($cardIds[$cardId]);
        if (empty($cardIds)) {
            Mage::getSingleton('giftcards/session')->clear();
        }
        $oSession->setGiftCardBalance($newSessionBalance);
        $oSession->setGiftCardsIds($cardIds);

        return;
    }

    public function getCollectionGiftCardFromOrderSaved($orderIncrementId) {
        $salesFlatOrder = Mage::getSingleton('core/resource')->getTableName('sales/order');
        $collection = Mage::getModel('giftcards/order')->getCollection();
//                ->addFieldToFilter('id_giftcard', $card->getId());
        $collection->getSelect()->join($salesFlatOrder, $salesFlatOrder . '.entity_id=main_table.id_order', $salesFlatOrder . '.increment_id as increment_id');
        $collection->addFieldToFilter('increment_id', array('eq' => $orderIncrementId));
        return $collection;
    }

    public function revertCard($orderId) {
        $collection = Mage::getModel('giftcards/order')->getCollection()->load($orderId, 'id_order');
        $dataOrder = array();
        foreach ($collection as $gift)
            $discountFromOrder = $gift->getData('discounted');
        $cardIdFromOrder = $gift->getData('id_giftcard');
        $dataOrder[] = array(
            'cardId' => $cardIdFromOrder,
            'discount' => $discountFromOrder
        );
        foreach ($dataOrder as $card) {
            $this->changeBalanceAndStatus($card['cardId'], $card['discount']);
        }

    }

    private function changeBalanceAndStatus($cardId, $discount) {
        $model = Mage::getModel('giftcards/giftcards')->load($cardId);
        $model->setData('card_status', '1');
        $oldBalance = $model->getData('card_balance');
        $newBalace = $oldBalance + $discount;
        $model->setData('card_balance', $newBalace);
        $model->save();
    }

    /*end Integrate with Webtex GiftCard*/

    /*TODO: Integrate with Mage world RewardPoints*/

    public function isIntegrateMageWorldRp() {
        if (Mage::getStoreConfig('rewardpoints/config/enabled')) {
            /*Check something here*/
            if (Mage::getStoreConfig('xpos/integrates/reward_points_integrate') == 'Mageworld')
                return true;
            else
                return false;

        } else {
            return false;
        }
    }


    /*TODO: Integrate with Webtex gift registry */

    public function isIntegrateWebtexGiftRegistry() {
        return false;
    }


    private $_productCollection;

    /**
     * @param $giftRegistryId
     * @return null | Product Collection
     */
    public function getItemsCollectionByGiftRegestryId($giftRegistryId) {
        $collection = Mage::getModel('webtexgiftregistry/webtexgiftregistry')->getCollection();
        $collection->getSelect()->where('`giftregistry_id` = ?', $giftRegistryId);
        if ($collection->getSize()) {
            $this->_getSession()->addSuccess('Found gift registry'
            );
            if (is_null($this->_productCollection)) {
                $registryId = $this->getRegistryId($giftRegistryId);
                $this->_productCollection = Mage::helper('webtexgiftregistry')->getItemsCollection($registryId);
            }

            return $this->_productCollection;
        } else {
            $this->_getSession()->addError('Not Found gift registry'
            );
            return null;
        }
    }

    public function getRegistryId($giftRegistryId) {
        $registry = Mage::getModel('webtexgiftregistry/webtexgiftregistry')->load($giftRegistryId, 'giftregistry_id');
        return $registry->getData('registry_id');
    }

    /**
     * @param $giftRegistryId
     * @param $productId
     * @return int|null
     */
    public function getGiftRegistryItemId($giftRegistryId, $productId) {
        $collection = Mage::getModel('webtexgiftregistry/item')->getCollection()->addFieldToFilter('giftregistry_id', array('eq' => $giftRegistryId))
            ->addFieldToFilter('product_id', array('eq' => $productId))->getFirstItem();
        $data = $collection->getData('giftregistry_item_id');
        if ($data)
            return $data;
        else
            return null;
    }

    public function giftRegistrySaveOrderAfter($order, $quote) {
        $giftRegistryId = Mage::getSingleton('adminhtml/session')->getGiftRegistry();
        if (!!$giftRegistryId) {
            if ($quote) {
                $send_to_registry_id = 0;
                $giftItems = false;
                foreach ($quote->getAllVisibleItems() as $visitem) {
                    $webtex_giftregistry_id = $giftRegistryId;
                    $productId = $visitem->getProductId();
                    $registryId = $this->getRegistryId($giftRegistryId);
                    $webtex_giftregistry_item_id = $this->getGiftRegistryItemId($registryId, $productId);
                    $item = Mage::getModel('webtexgiftregistry/item')->load($webtex_giftregistry_item_id);
                    if (!$item) {
                        continue;
                    }
                    $received = $item->getReceived();
                    $purchased = $item->getPurchased();

                    $data['received'] = $received + $visitem->getQty();
                    $data['purchased'] = $purchased + $visitem->getQty();

                    $model = Mage::getModel('webtexgiftregistry/item');
                    $model->setData($data)->setId($webtex_giftregistry_item_id);


                    if (Mage::helper('webtexgiftregistry')->isItemInRegistry($webtex_giftregistry_item_id, $registryId)) {
                        if ($received >= $item->getDesired()) {
                            Mage::log('item already purchased; order #: ' . $order->getIncrementId() . ', item id: ' . $visitem->getId() . ', registry item id = ' . $webtex_giftregistry_item_id . ', registry id = ' . $webtex_giftregistry_id, null, 'webtexgiftregistry.log');

                            $this->_getSession()->addError('Sorry, but one of the products you wanted to buy has already been purched by someone else.');
                            return;
                        } else {
                            try {
                                $model->save();
                                if (!$send_to_registry_id) {
//                                            $send_to_registry_id = $webtex_giftregistry_id;
                                    $send_to_registry_id = $registryId;
                                }
                                $giftItems = true;
                            } catch (Exception $e) {
                                Mage::getSingleton('adminhtml/session')->setGiftRegistry(null);
                                Mage::log('catch 1, order #: ' . $order->getIncrementId() . ', item id: ' . $visitem->getId() . ', message: ' . $e->getMessage(), null, 'webtexgiftregistry.log');
                            }
                        }
                    } else {
                        Mage::getSingleton('adminhtml/session')->setGiftRegistry(null);
                        Mage::log('item not in registry; order #: ' . $order->getIncrementId() . ', item id: ' . $visitem->getId() . ', registry item id = ' . $webtex_giftregistry_item_id . ', registry id = ' . $webtex_giftregistry_id, null, 'webtexgiftregistry.log');
                    }
                }

                $registry = Mage::getModel('webtexgiftregistry/webtexgiftregistry')->load($send_to_registry_id);
                if ($giftItems && $registry->getSendEmailNotifications()) {
                    /* @var $email Webtex_GiftRegistry_Model_Notification_Email_Template_PurchasedItem */
                    $email = Mage::getModel('webtexgiftregistry/notification_email_template_purchasedItem');
                    $email
                        ->setRegistry($registry)
                        ->notify($order);
                } else {
                    Mage::getSingleton('adminhtml/session')->setGiftRegistry(null);
                    Mage::log('notification not sent; order #: ' . $order->getIncrementId() . ', registry id = ' . $send_to_registry_id, null, 'webtexgiftregistry.log');
                }

                $model = Mage::getModel('webtexgiftregistry/orders');
                $lastOrderId = $order->getIncrementId();
                foreach ($quote->getAllVisibleItems() as $visitem) {

                    $webtex_giftregistry_id = $this->getRegistryId($giftRegistryId);

                    $data['giftregistry_id'] = $webtex_giftregistry_id;
                    $data['product_id'] = $visitem->getProductId();
                    $data['order_id'] = $lastOrderId;

                    $model->setData($data);

                    try {
                        $model->save();
                    } catch (Exception $e) {
                        Mage::getSingleton('adminhtml/session')->setGiftRegistry(null);
                        Mage::log('catch 2, order #: ' . $order->getIncrementId() . ', item id: ' . $visitem->getId() . ', message: ' . $e->getMessage(), null, 'webtexgiftregistry.log');
                    }
                }

            }
        }
        Mage::getSingleton('adminhtml/session')->setGiftRegistry(null);
    }


    /*TODO: Integrate with Rack RewardPoint*/

    public function isIntegrateRackRp() {
        if (Mage::getStoreConfig('rackpoint/config/enable')) {
//            Check something
            if (Mage::getStoreConfig('xpos/integrates/reward_points_integrate') == 'Rack')
                return true;
            else
                return false;
        } else
            return false;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     * @throws Mage_Core_Exception
     */
    public function processData(Varien_Event_Observer $observer) {
        if (!$this->isIntegrateRackRp()) {
            return $this;
        }

        $data = new Varien_Object($observer->getEvent()->getRequest());
        $input = new Varien_Object($data->getPayment());
        /**
         * @var $orderCreateObject Mage_Adminhtml_Model_Sales_Order_Create
         */
        $orderCreateObject = $observer->getEvent()->getOrderCreateModel();
        $quote = $orderCreateObject->getQuote();
        $request = $observer->getEvent()->getRequest();
        if (isset($request['order']))
            $order = $request['order'];
        else
            return $this;
        if (isset($order['rackRp'])) {
            $rackpoint = $order['rackRp'];
            if (!is_nan($rackpoint['amount']) && $rackpoint['amount'] > 0) {
                $usedPoint = true;
            }
            if ($quote->getGrandTotal() < (int)$rackpoint['amount']) {
                $usedPoint = false;
                $orderCreateObject->getSession()->setPointUsed(false);
                $this->_getSession()->addError('Please check reward point use.'
                );
            }
        } else
            $usedPoint = false;


        $quote->setIsUsingPoints($usedPoint);
        if ($quote->getIsUsingPoints()) {
            $quote->setPointsTotalReseted(true);
            $usedPoint = (int)$rackpoint['amount'];

            $minRequiredPoint = Mage::helper('rackpoint')->getMinRequiredPoint();
            if ($minRequiredPoint > 0 && $usedPoint < $minRequiredPoint) {
                Mage::throwException(Mage::helper('rackpoint')->__('Used point is lower than minimum required point %s.', number_format(Mage::getStoreConfig('rackpoint/config/min_required_point'))));
            }
            $quote->setPointUsed($usedPoint);
            $websiteId = $quote->getStore()->getWebsiteId();
            /* @var $point Rack_Point_Model_Point_Balance */
            $point = Mage::getModel('rackpoint/point_balance')->loadByCustomerId($quote->getCustomerId(), $websiteId);
            if ($point->getId()) {
                $quote->setPointInstance($point);
//                if (!$input->getMethod()) {
//                    $quote->getPayment()->setMethod('free');
//                    $input->setMethod('free');
//                }
                $orderCreateObject->getSession()->setPointUsed($rackpoint['amount']);
                $quote->collectTotals()->save();
                $quote->setPointsTotalReseted(true);
            } else {
                $orderCreateObject->getSession()->setPointUsed(false);
                $quote->setIsUsingPoints(false);
            }
        } else {
            $orderCreateObject->getSession()->setPointUsed(false);
        }
    }


    /*TODO: Integrate with Gift Voucher (Gift Card MageStore)*/
    public function isIntegrateWithGiftVoucher() {
        if (Mage::getStoreConfig('xpos/integrates/giftt_card_integrate') == SM_XPos_Model_Adminhtml_System_Config_SourceModel_GiftCardIntegrate::$MAGESTORE_GIFTVOUCHER)
            return true;
        else
            return false;
    }


    protected function _getOrderCreateModel() {
        return Mage::getSingleton('xpos/sales_create');
    }


    public function activeGiftVoucher($code) {
        $session = Mage::getSingleton('checkout/session');
        $quote = $this->_getOrderCreateModel()->getQuote();
        if ($quote->getCouponCode() && !Mage::helper('giftvoucher')->getGeneralConfig('use_with_coupon')) {
            $this->clearGiftcardSession($session);
            $this->_getSession()->addError('A coupon code has been used. You cannot apply gift codes or Gift Card credit with the coupon to get discount.');
        } else {
            if($code){
                $session->setUseGiftCardCredit(0);
                $session->setMaxCreditUsed(null);
                $session->setUseGiftCard(1);
                $addcodes[] = $code;
                foreach ($addcodes as $code) {
                    $giftVoucher = Mage::getModel('giftvoucher/giftvoucher')->loadByCode($code);
                    $quote = $this->_getOrderCreateModel()->getQuote();
                    if ($giftVoucher->getBaseBalance() > 0
                        && $giftVoucher->getStatus() == Magestore_Giftvoucher_Model_Status::STATUS_ACTIVE
                        && $giftVoucher->validate($quote->setQuote($quote))
                    ) {
                        $giftVoucher->addToSession($session);
                        if ($giftVoucher->getCustomerId() == $this->_getOrderCreateModel()->getCustomerId()
                            && $giftVoucher->getRecipientName()
                            && $giftVoucher->getRecipientEmail() && $giftVoucher->getCustomerId()
                        ) {
                            $this->_getSession()->addError('Gift Card '.$code.' has been sent to the customer\'s friend.');
                        }
                        $this->_getSession()->addSuccess('Gift Card '.$code.' has been applied successfully.');
                    } else {
                        $this->_getSession()->addError('Gift Card '.$code.' is no longer available to use. ');
                    }
                }
            }
        }

    }

    /*convert from frontend*/
    public function activeGiftVoucher_old($code) {
        $quote = $this->_getOrderCreateModel()->getQuote();
        $session = Mage::getSingleton('checkout/session');

        if(isset($code)){
            $giftVoucher = Mage::getModel('giftvoucher/giftvoucher')->loadByCode($code);
            //$codes = Mage::getSingleton('giftvoucher/session')->getCodes();
            if (!$giftVoucher->getId()) {
                $this->_getSession()->addError('Gift card "'.$code.'" is invalid.');
                return;
            }

            if (!Mage::helper('giftvoucher')->isAvailableToAddCode()) {
                $this->_getSession()->addError('The maximum number of times to enter gift codes is: ' . Mage::helper('giftvoucher')->getGeneralConfig('maximum'));
                return;
            }
            if( $giftVoucher->getBaseBalance() <= 0
                && $giftVoucher->getStatus() != Magestore_Giftvoucher_Model_Status::STATUS_ACTIVE
            ){
                $this->_getSession()->addError('Gift code "'.$code.'" is no longer available to use.');
                return;
            }
            //Action
            //$hasCoupon = $this->getLayout()->getBlockSingleton('xpos/adminhtml_override_coupons')->getCouponCode();
            if ($quote->getCouponCode() && !Mage::helper('giftvoucher')->getGeneralConfig('use_with_coupon')) {
                $this->_getSession()->addError('A coupon code has been used. You cannot apply gift codes or Gift Card credit with the coupon to get discount');
                return;
            }
            else {
                //Xu ly
                $session->setUseGiftCardCredit(0);
                $session->setMaxCreditUsed(null);
                $session->setUseGiftCard(1);
                $quote = $this->_getOrderCreateModel()->getQuote();
                if ($giftVoucher->getBaseBalance() > 0
                    && $giftVoucher->getStatus() == Magestore_Giftvoucher_Model_Status::STATUS_ACTIVE) {
                    if (Mage::helper('giftvoucher')->canUseCode($giftVoucher)) {
                        $flag = false;
                        foreach ($quote->getAllItems() as $item) {
                            if ($giftVoucher->getActions()->validate($item)) {
                                $flag = true;
                            }
                        }
                        $giftVoucher->addToSession($session);
                        if ($giftVoucher->getCustomerId() == Mage::helper('xpos/configXPOS')->getDefaultCustomerId()
                            && $giftVoucher->getRecipientName() && $giftVoucher->getRecipientEmail()
                            && $giftVoucher->getCustomerId()
                        ) {
                            $this->_getSession()->addError('Please note that gift code "'.Mage::helper('giftvoucher')->getHiddenCode($code).'" has been sent to your friend. When using, both you and your friend will share the same balance in the gift code.');
                            return;
                        }
                        if ($flag && $giftVoucher->validate($quote->setQuote($quote))) {
                            $isGiftVoucher = true;
                            foreach ($quote->getAllItems() as $item) {
                                if ($item->getProductType() != 'giftvoucher') {
                                    $isGiftVoucher = false;
                                    break;
                                }
                            }
                            if (!$isGiftVoucher) {
                                $this->_getSession()->addSuccess('Gift code "'.Mage::helper('giftvoucher')->getHiddenCode($code).'" has been applied successfully.');
                                return;
                            } else {
                                $this->_getSession()->addError('Please remove your Gift Card information since you cannot use either gift codes or Gift Card credit balance to purchase other Gift Card products.');
                                return;
                            }
                        } else {
                            $this->_getSession()->addError('You can’t use this gift code since its conditions haven’t been met.');
                            return;
                        }
                    } else {
                        $this->_getSession()->addError('This gift code limits the number of users');
                        return;
                        // Mage::helper('giftvoucher')->getHiddenCode($code);
                    }
                }
                //Xu ly xong
            }
        }

    }

    public function deGiftVoucher($code) {
        $session = Mage::getSingleton('checkout/session');
        $codes = trim($session->getGiftCodes());
        $success = false;
        if ($code && $codes) {
            $codesArray = explode(',', $codes);
            foreach ($codesArray as $key => $value) {
                if ($value == $code) {
                    unset($codesArray[$key]);
                    $success = true;
                    $giftMaxUseAmount = unserialize($session->getGiftMaxUseAmount());
                    if (is_array($giftMaxUseAmount) && array_key_exists($code, $giftMaxUseAmount)) {
                        unset($giftMaxUseAmount[$code]);
                        $session->setGiftMaxUseAmount(serialize($giftMaxUseAmount));
                    }
                    break;
                }
            }
        }
        if ($success) {
            $codes = implode(',', $codesArray);
            $session->setGiftCodes($codes);
            $this->_getSession()->addSuccess('Gift Card "'.Mage::helper('giftvoucher')->getHiddenCode($code).'" has been removed successfully!');
        } else {
            $this->_getSession()->addError('Gift card "'.$code.'" not found!');
        }
    }

    /**
     * Clear Gift Card session
     */
    public function clearGiftcardSession($session)
    {
        if ($session->getUseGiftCard()) {
            $session->setUseGiftCard(null)
                ->setGiftCodes(null)
                ->setBaseAmountUsed(null)
                ->setBaseGiftVoucherDiscount(null)
                ->setGiftVoucherDiscount(null)
                ->setCodesBaseDiscount(null)
                ->setCodesDiscount(null)
                ->setGiftMaxUseAmount(null);
        }
        if ($session->getUseGiftCardCredit()) {
            $session->setUseGiftCardCredit(null)
                ->setMaxCreditUsed(null)
                ->setBaseUseGiftCreditAmount(null)
                ->setUseGiftCreditAmount(null);
        }
    }
    /* end Integrate with Gift Voucher (Gift Card Magento)*/
}
