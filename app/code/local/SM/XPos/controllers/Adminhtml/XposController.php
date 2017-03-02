<?php

require_once(BP . DS . 'app' . DS . 'code' . DS . 'core' . DS . 'Mage' . DS . 'Adminhtml' . DS . 'controllers' . DS . 'Sales' . DS . 'Order' . DS . 'CreateController.php');

class SM_XPos_Adminhtml_XposController extends Mage_Adminhtml_Sales_Order_CreateController
{
    const PENDING_STATUS = 'pending';
    protected $_billingOrigin;
    protected $_validated = false;
    protected $_storeid;
    public static $_firstLoad4B = false;
    public static $_callFromControllerXpos = false;

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/sales/xpos/mainxpos');
    }

    protected function _getOrderCreateModel()
    {
        return Mage::getSingleton('xpos/sales_create');
    }

    protected function _initSession()
    {
        /*TODO: integrate MageStoreRP*/
        if ($customerId = $this->getRequest()->getParam('customer_id')) {
            Mage::getModel('xpos/integrate')->setSessionCustomer($customerId);
        }
        /**
         * Identify warehouse
         */

        $warehouseId = Mage::getSingleton('adminhtml/session')->getWarehouseId();
        $this->_getSession()->setWarehouseId((int)$warehouseId);

        return parent::_initSession();
    }

    protected function _construct()
    {
        parent::_construct();
        if (Mage::helper('xpos')->aboveVersion('1.6')) {
            $this->_billingOrigin['city'] = Mage::getStoreConfig(Mage_Shipping_Model_Config::XML_PATH_ORIGIN_CITY);
            $this->_billingOrigin['country_id'] = Mage::getStoreConfig(Mage_Shipping_Model_Config::XML_PATH_ORIGIN_COUNTRY_ID);
            $this->_billingOrigin['region'] = Mage::getStoreConfig(Mage_Shipping_Model_Config::XML_PATH_ORIGIN_REGION_ID);
            $this->_billingOrigin['postcode'] = Mage::getStoreConfig(Mage_Shipping_Model_Config::XML_PATH_ORIGIN_POSTCODE);
        } else {
            $this->_billingOrigin['city'] = Mage::getStoreConfig('shipping/origin/city');
            $this->_billingOrigin['country_id'] = Mage::getStoreConfig('shipping/origin/country_id');
            $this->_billingOrigin['region'] = Mage::getStoreConfig('shipping/origin/region_id');
            $this->_billingOrigin['postcode'] = Mage::getStoreConfig('shipping/origin/postcode');
        }
//        $this->_storeid = Mage::helper('xpos/product')->getCurrentSessionStoreId();
    }

    protected function _validateSecretKey()
    {
        return true;
    }

    protected function _checkLicense()
    {
        if (!Mage::helper('xpos/license')->checkLicense("xpos", Mage::getStoreConfig('xpos/general/key'))) {
            return $this->_redirect("adminhtml/system_config/edit/section/xpos");
        } else {
            $this->_validated = true;
        }
    }

    protected function _initAction()
    {
        /*$this->loadLayout();
        return $this;*/
    }

    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);

            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);

        return $order;
    }

    protected function _initCreditmemo($update = false)
    {
        $this->_title($this->__('Sales'))->_title($this->__('Credit Memos'));

        $creditmemo = false;
        $creditmemoId = $this->getRequest()->getParam('creditmemo_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if ($creditmemoId) {
            $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
        } elseif ($orderId) {
            $data = $this->getRequest()->getParam('creditmemo');
            $order = Mage::getModel('sales/order')->load($orderId);
            $invoice = $this->_initInvoice($order);

            $savedData = $this->_getItemData();

            $qtys = array();
            $backToStock = array();
            foreach ($savedData as $orderItemId => $itemData) {
                if (isset($itemData['qty'])) {
                    $qtys[$orderItemId] = $itemData['qty'];
                }
                if (isset($itemData['back_to_stock'])) {
                    $backToStock[$orderItemId] = true;
                }
            }
            $data['qtys'] = $qtys;

            $service = Mage::getModel('sales/service_order', $order);
            if ($invoice) {
                $creditmemo = $service->prepareInvoiceCreditmemo($invoice, $data);
            } else {
                $creditmemo = $service->prepareCreditmemo($data);
            }

            /**
             * Process back to stock flags
             */
            foreach ($creditmemo->getAllItems() as $creditmemoItem) {
                $orderItem = $creditmemoItem->getOrderItem();
                $parentId = $orderItem->getParentItemId();
                if (isset($backToStock[$orderItem->getId()])) {
                    $creditmemoItem->setBackToStock(true);
                } elseif ($orderItem->getParentItem() && isset($backToStock[$parentId]) && $backToStock[$parentId]) {
                    $creditmemoItem->setBackToStock(true);
                } elseif (empty($savedData)) {
                    $creditmemoItem->setBackToStock(Mage::helper('cataloginventory')->isAutoReturnEnabled());
                } else {
                    $creditmemoItem->setBackToStock(false);
                }
            }
        }

        $args = array('creditmemo' => $creditmemo, 'request' => $this->getRequest());
        Mage::dispatchEvent('adminhtml_sales_order_creditmemo_register_before', $args);

        Mage::register('current_creditmemo', $creditmemo);

        return $creditmemo;
    }

    protected function _initInvoice($order)
    {
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        if ($invoiceId) {
            $invoice = Mage::getModel('sales/order_invoice')
                ->load($invoiceId)
                ->setOrder($order);
            if ($invoice->getId()) {
                return $invoice;
            }
        }

        return false;
    }

    protected function _getItemData()
    {
        $data = $this->getRequest()->getParam('creditmemo');
        if (!$data) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        }

        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = array();
        }

        return $qtys;
    }

    protected function _getItemQtys()
    {
        $data = $this->getRequest()->getParam('order');
        if (isset($data['items'])) {
            $qtys = $data['items'];
        } else {
            $qtys = array();
        }

        return $qtys;
    }

    protected function _processData($action = null)
    {
        if ($action != 'save') {
            if (Mage::helper('xpos')->aboveVersion('1.6')) {
                $data = $this->getRequest()->getParams();
                $order = $this->getRequest()->getParam('order');
                /*TODO: Integrate with Gift Voucher (Gift Card Magento)*/
                if (Mage::getModel('xpos/integrate')->isIntegrateWithGiftVoucher()) {
                    if (isset($order['giftvoucher'])) {
                        Mage::getModel('xpos/integrate')->activeGiftVoucher(trim($order['giftvoucher']['code']));
                    }
                    if (isset($order['giftvoucherRemove'])) {
                        Mage::getModel('xpos/integrate')->deGiftVoucher($order['giftvoucherRemove']['code']);
                    }
                }
                /*TODO: Integrate with webtex giftcard*/
                if (Mage::getModel('xpos/integrate')->isIntegrateWithWebtexGiftCard()) {
                    $order = $this->getRequest()->getParam('order');
                    if (isset($order['wtgiftcard'])) {
                        $giftCard = $order['wtgiftcard'];
                        Mage::getModel('xpos/integrate')->activeGiftCards($giftCard['code']);
                    }
                    if (isset($order['wtgiftcardRemove'])) {
                        $giftCard = $order['wtgiftcardRemove'];
                        Mage::getModel('xpos/integrate')->deActiveGiftCard($giftCard['id']);
                    }
                }
                /*end integrate*/

                $quote = $this->_getOrderCreateModel()->getQuote();
                if (isset($data['customer_id']) && $data['customer_id'] != "false" && intval($data['customer_id'])) {
                    $customer = Mage::getModel('customer/customer')->load($data['customer_id']);
                    if ($quote->getCustomerGroupId() == null) {
                        $quote->setCustomerGroupId(0);
                    }
                    $quote->assignCustomer($customer);
                    Mage::register('xpos_order', $customer->getData('group_id'));
                } else {
                    $quote->setCustomerId(0);
                    $quote->setCustomerTaxClassId(0);
                    $quote->setCustomerGroupId(0);
                    $quote->setCustomerEmail('');
                    $quote->setCustomerFirstname('');
                    $quote->setCustomerLastname('');
                    Mage::register('xpos_order', 0);
                }
                $startTime = microtime();
                /** @var Mage_Adminhtml_Block_Sales_Order_Create_Shipping_Method_Form $shippingBlock */
//                $shippingBlock = $this->getLayout()->createBlock('adminhtml/sales_order_create_shipping_method_form');
                /*-------------------- SonBv cu chuoi-----------------*/
                // Set default shipping method
//                if (!$shippingBlock->getActiveMethodRate()) {
//                    $defaultShipping = Mage::getStoreConfig('xpos/general/default_shipping', null);
//                    if ($defaultShipping || array_key_exists($defaultShipping, $shippingBlock->getShippingRates())) {
//                        $shippingBlock->getAddress()->setShippingMethod($defaultShipping/* . '_' . $defaultShipping*/);
//                    } elseif (count($shippingBlock->getShippingRates())) {
//                        $defaultShipping = current(array_keys($shippingBlock->getShippingRates()));
//                        $shippingBlock->getAddress()->setShippingMethod($defaultShipping/* . '_' . $defaultShipping*/);
//                    }
//                }
//                $this->_getOrderCreateModel()->setRecollect(true);


                //code to update configurable product custom price
                $cur_quote = $quote = $this->_getOrderCreateModel()->getQuote()->getId();
                $items = Mage::getModel('sales/quote_item')->getCollection()
                    ->addFieldToFilter('quote_id', array('eq' => $cur_quote))
                    ->getData();
                parent::_processActionData($action);

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
                                $data = $this->_processFiles($data);
                                $this->_getOrderCreateModel()->updateQuoteItemsConfig($data);
                            }
                        }
                    }
                }
            } else {
                parent::_processData();
            }
        }
        if ($action == 'save') {
            // set Store
            $this->_storeid = Mage::helper('xpos/product')->getCurrentSessionStoreId();
            $quote = $this->_getOrderCreateModel()->getQuote();
            if ($storeid = $this->getRequest()->getPost('store_id'))
                $quote->setStoreId($storeid);
            else
                $quote->setStoreId($this->_storeid);

            parent::_getOrderCreateModel()->initRuleData();

            $data = $this->getRequest()->getPost('order');
            $couponCode = $this->_processDiscount($data);
            /*begin check new customer*/
            if ($data['account']['type'] == 'new') {
                $customer = Mage::getModel('customer/customer');
                $store = Mage::getModel('core/store')->load(Mage::helper('xpos/product')->getCurrentSessionStoreId());
                $website = Mage::getModel('core/store')->load(Mage::helper('xpos/product')->getCurrentSessionStoreId())->getWebsiteId();
                $customer->setWebsiteId($website);
                $email_temp = $data['account']['email_temp'];
                $customer->loadByEmail($email_temp);
                if (!$customer->getId() && $email_temp != '') {
                    $billingAddress = $data['billing_address'];
                    $shippingAddress = $data['shipping_address'];
                    $firstName = $billingAddress['firstname'];
                    $lastName = $billingAddress['lastname'];
                    $pass = '12345678abc';
                    $customer->setStore($store);
                    $customer->setEmail($email_temp);
                    $customer->setFirstname($firstName);
                    $customer->setLastname($lastName);
                    $customer->setPassword($pass);
                    $isXman = 0;
                    if (Mage::getStoreConfig('xmanager/general/enabled') == 1) {
                        /*xmanager has been installed*/
                        $isXman = 1;
                    }
                    if ($isXman == 1) {
                        $per = Mage::getModel('xmanager/permission');
                        $currentAdmin = $per->getCurrentAdmin();
                        $adminId = $currentAdmin['id'];
                        $customer->setData('admin_id', $adminId);
                    }
                    $customer->save();

                    // set Default Billing And Shipping:
                    $_billingAddress = Mage::getModel('customer/address');
                    $_billingAddress->setData($billingAddress)
                        ->setCustomerId($customer->getId())
                        ->setIsDefaultBilling('1')
                        ->setSaveInAddressBook('1');
                    $_billingAddress->save();

                    $_shippingAddress = Mage::getModel('customer/address');
                    $_shippingAddress->setData($shippingAddress)
                        ->setCustomerId($customer->getId())
                        ->setIsDefaultShipping('1')
                        ->setSaveInAddressBook('1');
                    $_shippingAddress->save();

//                    set customer Id
                    $data['customer_id'] = $customer->getId();

                } else {
                    $data['customer_id'] = $customer->getId();
                }
            }
            /*end check new customer*/
            if (!Mage::registry('rule_data'))
                $this->_getOrderCreateModel()->initRuleData();
//            if (isset($data['customer_id']) && intval($data['customer_id'])) {
//                $this->_getSession()->setCustomerId((int)$data['customer_id']);
//                $customer = Mage::getModel('customer/customer')->load($data['customer_id']);
//                $quote->assignCustomer($customer);
//                Mage::register('xpos_order', $customer->getData('group_id'));
//            } else {
//                $quote->setCustomerGroupId(0);
//                Mage::register('xpos_order', 0);
//            }

            $defaultShipping = Mage::helper('xpos/configXPOS')->getDefaultShipping();
            $shippingMethod = $this->_getOrderCreateModel()->getQuote()->getShippingAddress()->getShippingMethod();
            if (!$shippingMethod) {
                if (!isset($data['shipping_method'])) {
                    $data['shipping_method'] = $defaultShipping;
                }
                Mage::getSingleton('adminhtml/sales_order_create')->setShippingMethod($data['shipping_method'])->collectShippingRates();
            } else {
                // in case save order no complete
//                Mage::getSingleton('adminhtml/sales_order_create')->setShippingMethod($data['shipping_method'])->collectShippingRates();
            }

            $data['shipping_method'] = $this->_getOrderCreateModel()->getQuote()->getShippingAddress()->getShippingMethod();

            if (Mage::getModel('xpos/integrate')->isIntegrateRackRp()) {
                /*Must recollect total after set point use. Because Rackpoint extension save point use to $quote object. It Will be remove after request.
                */
                $quote = $this->_getQuote();
                $point = $quote->getPointUsed();
                /* After apply point and collect total => RackPoint will check something.(Not want to explode). Code below to add something to recollect total*/
                if ($point > 0) {
                    $quote->setPointsTotalReseted(true);
                    $usedPoint = $point;

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
                        $orderCreateObject = $this->_getOrderCreateModel();
                        $orderCreateObject->getSession()->setPointUsed($usedPoint);
                        $quote->collectTotals()->save();
                        $quote->setPointsTotalReseted(true);
                    }
                    $this->_getQuote()->collectTotals();
                }
            }

            /*
             * Set Data in post pament to Mage_Payment_Model_Method_Abstract
             * */
            $isSplitPayment = false;
            $paymentData = $this->getRequest()->getPost('payment');
            $paymentMethod = $paymentData['method'];
            $orderDiscount = abs($this->getRequest()->getPost('order_discount'));

            if (Mage::getStoreConfig('xpayment/xpayment_discount/active') == 1 && $orderDiscount != 0) {
                $granTotal = $this->_getOrderCreateModel()->getQuote()->getGrandTotal();
                $paymentData['splitPayment']['xpayment_discount'] = $orderDiscount;
                $paymentData['splitPayment']['enable'] = 1;
                if ($paymentMethod != 'xpaymentMultiple' && $granTotal > 0) {
                    $paymentData['splitPayment'][$paymentData['method']] = $granTotal;
                }
                $paymentData['method'] = 'xpaymentMultiple';
                $paymentMethod = $paymentData['method'];
            }

            if ($paymentMethod == 'xpaymentMultiple') {
                $isSplitPayment = true;
            }
            if ($paymentData && !$isSplitPayment) {
                unset($paymentData['splitPayment']);
//                if (!$quote->getPayment()->getMethodInstance()) {
//                Mage::getSingleton('adminhtml/session_quote')->getQuote()->setTotalsCollectedFlag(true);
//                $this->_getOrderCreateModel()->setPaymentData($paymentData);
                $this->_getOrderCreateModel()->getQuote()->getPayment()->setMethod($paymentMethod);
//                }
            } else {
                if ($paymentData == null) {
                    $defaultPaymentInConfig = Mage::helper('xpos/configXPOS')->getDefaultPayment($this->_storeid);
                    $this->_getOrderCreateModel()->setPaymentMethod($defaultPaymentInConfig);
                } else {
                    $splitPaymentData = $paymentData['splitPayment'];
                    $this->_getOrderCreateModel()->setPaymentMethod('xpaymentMultiple');
                    $this->_getOrderCreateModel()->setPaymentData($splitPaymentData);
                }
            }
            $quote->collectTotals();
            $quote->save();

            // remove coupon if has
            if ($couponCode) {
                $couponCode->setIsActive(0)->save();
            }
            return $data;
        }
    }

    public function getIdQuoteAction()
    {
        $id_quote = $this->getRequest()->getPost('quote_item_id');
        $quote_item = Mage::getModel('sales/quote_item')->load($id_quote);
        //  echo "<pre>";
        //var_dump($quote_item->getData());die;
        $product_id = $quote_item->getData('product_id');
        $result = array();
        $result['product_id'] = $product_id;
//        $this->getResponse()->setBody();
        echo Mage::helper('core')->jsonEncode($result);
        die();
    }

    public function calDiscountAction()
    {
        $data = array();
        $this->_processDiscount($data);
    }

    protected function _processDiscount($data)
    {
        $couponCode = false;
        $customDiscount = floatval($this->getRequest()->getPost('custom-discount'));
        $quote = $this->_getOrderCreateModel()->getQuote();

        if ($customDiscount > 0) {
            $model = Mage::getModel('salesrule/coupon');
            $name = 'X-POS #' . $quote->getId();
            $couponCode = substr(md5($name . microtime()), 0, 8);
            $website_id = intval(Mage::getModel('core/store')->load(Mage::helper('xpos/product')->getCurrentSessionStoreId())->getWebsiteId());
            //$website_id = Mage::app()->getWebsite()->getId();
            // create coupon
            $currentTimestamp = Mage::getModel('core/date')->timestamp(time());
            $date = date('Y-m-d', $currentTimestamp);
            $rule = Mage::getModel('salesrule/rule')
                ->setName($name)
                ->setDescription('Coupon for X-POS')
                ->setCustomerGroupIds($this->_getCustomerGroups())
                ->setFromDate($date)
                ->setUsesPerCoupon(1)
                ->setUsesPerCustomer(1)
                ->setIsActive(1)
                ->setSimpleAction(Mage_SalesRule_Model_Rule::CART_FIXED_ACTION)
                ->setDiscountAmount($customDiscount)
                ->setStopRulesProcessing(0)
                ->setIsRss(0)
                ->setWebsiteIds($website_id)
                ->setCouponType(Mage_SalesRule_Model_Rule::COUPON_TYPE_SPECIFIC)
                ->save();

            $model->setRuleId($rule->getId())
                ->setCode($couponCode)
                ->setIsPrimary(1)
                ->save();
        }
        if ($couponCode) {
            $data['coupon']['code'] = $couponCode;
            $this->_getOrderCreateModel()
                ->importPostData($data);
        }
        $result = array();
        $result['new_discount'] = $customDiscount;
        $result['couponCode'] = $couponCode;
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

        return $couponCode ? $rule : false;
    }

    protected function _getCustomerGroups()
    {
        $groupIds = array();
        $collection = Mage::getModel('customer/group')->getCollection();
        foreach ($collection as $customer) {
            $groupIds[] = $customer->getId();
        }

        return $groupIds;
    }

    protected function _clear_current_order_cache()
    {
        Mage::getSingleton('adminhtml/session')->setSelectedShipmentMethod(null);
        unset($_SESSION['xpos_loaded_order_id']);
    }

    public function selectCustomerAction()
    {
        $this->loadLayout()->renderLayout();
    }

    public function indexAction()
    {

        if (intval(Mage::helper("xpos")->isEnableModule()) == 0) {
            return $this->_redirect("adminhtml/system_config/edit/section/xpos");
        }

        $orderId = $this->getRequest()->getParam('order_id');

        if (Mage::getSingleton('adminhtml/sales_order_create')->getQuote()->getId() == null) {
            $this->clearAction();
        }

        if ($orderId) {
            //clear
            $items =  $quote = $this->_getOrderCreateModel()->getQuote()->getItemsCollection();
            foreach ($items as $item) {
                $this->_getOrderCreateModel()->getQuote()->removeItem($item->getId());
            }
            Mage::getSingleton('adminhtml/session')->unsetData('xpos_discount',0);
            $this->_clearAll();
        $order = Mage::getModel('sales/order')->load($orderId);

        if ($order->getId()) {
            //$this->_getSession()->clear();
            $order->setReordered(true);
            if (Mage::getStoreConfig('cataloginventory/options/can_back_in_stock') == 1) {
                $order->cancel();
            }
            $this->_getOrderCreateModel()->initFromOrder($order);
            if ($order->getData('status') == 'pending' || $order->getData('state') == 'pending') {
                $order->setData('state', Mage_Sales_Model_Order::STATE_CANCELED);
                $order->setStatus("canceled");
            }

            /*FIXME: Check file account.phtml */
            /*DO: set session customer*/
            Mage::getSingleton('adminhtml/session')->setCustomerIdReload($customerId = $order->getCustomerId());

            /*TODO:Integrate with webtex GIft card in case Load save Oreder*/
            if (Mage::getModel('xpos/integrate')->isIntegrateWithWebtexGiftCard()) {
                $orderIncrementId = $order->getIncrementId();
                $orderEntityId = $order->getData('entity_id');
                /*Revert balanece*/
                Mage::getModel('xpos/integrate')->revertCard($orderEntityId);
                $collection = Mage::getModel('xpos/integrate')->getCollectionGiftCardFromOrderSaved($orderIncrementId);
                foreach ($collection as $giftCard) {
                    $cardCode = Mage::getModel('giftcards/giftcards')->load($giftCard->getData('id_giftcard'))->getData('card_code');
                    Mage::getModel('xpos/integrate')->activeGiftCards($cardCode);
                }
            }
            $order->save();

            /*FIXME:*/
            return $this->_redirect('*/*');
        }
    } else
{
//            Mage::getSingleton('adminhtml/session')->setCustomerIdReload(null);
$this->_storeid = Mage::helper('xpos/product')->getCurrentSessionStoreId();
$this->_getSession()->setStoreId($this->_storeid);
$this->_getSession()->setCustomerId(false);
$_quoteId = $this->_getOrderCreateModel()->getQuote()->getId();
$this->_getOrderCreateModel()->getQuote()->setStoreId($this->_storeid);
$defaultCustomerId = Mage::helper('xpos/configXPOS')->getDefaultCustomerId();
Mage::getModel('xpos/integrate')->setSessionCustomer($defaultCustomerId);
//            $data_default = array(
//                'account' => array(
//                    'group_id' => 1
//                ),
//                'billing_address' => array(
//                    'firstname' => 'Guest ' . $_quoteId,
//                    'lastname' => 'Guest ' . $_quoteId,
//                    'street' => array('Guest Address'),
//                    'city' => (empty($this->_billingOrigin['city']) ? 'Guest City' : $this->_billingOrigin['city']),
//                    'country_id' => (empty($this->_billingOrigin['country_id']) ? 'Guest City' : $this->_billingOrigin['country_id']),
//                    'region' => (empty($this->_billingOrigin['region']) ? 'CA' : $this->_billingOrigin['region']),
//                    'postcode' => (empty($this->_billingOrigin['postcode']) ? '95064' : $this->_billingOrigin['postcode']),
//                    'telephone' => (Mage::getStoreConfig('general/store_information/phone') == "" ? '1234567890' : Mage::getStoreConfig('general/store_information/phone')),
//                )
//            );
//            $this->_getOrderCreateModel()->importPostData($data_default);
//            $this->_getOrderCreateModel()->setShippingAsBilling('on');
//            $this->_getOrderCreateModel()->resetShippingMethod(true);
    //$this->_getOrderCreateModel()->collectShippingRates();
$this->_getOrderCreateModel()
->saveQuote();
}

$this->loadLayout();

$_SESSION['blankcart'] = true;
if (count($this->_getOrderCreateModel()->getQuote()->getAllItems()) > 0) {
    $_SESSION['blankcart'] = false;
}

$this->_initAction();
$this->renderLayout();

}

private
function registerCustomShippingAmount($amountShip)
{
    if (!!$amountShip) {
        Mage::register('xpos_shipping_cost', $amountShip);
    }
}

public
function checkOrderAction()
{
    $this->_clear_current_order_cache();
    try {
        $data_all = $this->getRequest()->getPost();
        if (isset($data_all['shipment']['xpayment_pickup_shipping_xpayment_pickup_shipping'])) {
            $amountShip = $data_all['shipment']['xpayment_pickup_shipping_xpayment_pickup_shipping'];
            $this->registerCustomShippingAmount($amountShip);
        }

        SM_XPos_Adminhtml_XposController::$_callFromControllerXpos = true;
        $data = $this->_processData('save');

        $customer_name = Mage::getModel('customer/customer')->load($data['customer_id'])->getName();
//            $this->_getOrderCreateModel()->setRecollect(true);
        // fixed : cant save cc number when using authozisenet and ccsave
        $paymentData = $this->getRequest()->getPost('payment');


        if ($paymentData && isset($paymentData['method']) && (in_array($paymentData['method'], array('ccsave', 'authorizenet', 'verisign')))) {
            $this->_getOrderCreateModel()->setPaymentData($paymentData);
        }
        if (isset($data['shipping_method'])) {
            $this->_setShipping($data['shipping_method']);
        }

        Mage::getSingleton('adminhtml/sales_order_create')->collectShippingRates();
        $quote = $this->_getQuote();
        $order = $this->_getOrderCreateModel()
            ->setIsValidate(true)
            ->createOrder();

        /*TODO: Set to InfoOrder For print Invoice */
        $totalPaid = $this->getRequest()->getPost('cash-in');
        $totalRefunded = $this->getRequest()->getPost('balance');
        $grand_total = $this->_getQuote()->getGrandTotal();
        $info_order['entity_id'] = $order->getEntityId();
        $info_order['order_id'] = $order->getIncrementId();
        $info_order['totalPaid'] = 0;
        $info_order['sub_total'] = $order->getSubtotal();
        $info_order['grand_total'] = $grand_total;
        $info_order['tax_amount'] = $order->getTaxAmount();
        $info_order['balance'] = $totalRefunded;
        $info_order['balance'] = $data_all['balance'];
        $info_order['ship_amount'] = $order->getShippingAmount();
        $info_order['customer_name'] = $customer_name;
        $isInforOrder = true;
        if ($data_all['doinvoice']) {
            if ($isInforOrder) $info_order['totalPaid'] = $totalPaid;
        }
        Mage::register('info_order', $info_order);
        Mage::getSingleton('adminhtml/session')->setInfoOrder($info_order);

        $this->getResponse()->setBody(json_encode($info_order));
    } catch (Exception $e) {
        $this->_getSession()->addError($e->getMessage());
        $this->getResponse()->setBody(0);
    }
}

/*Create invoice and set total paid*/
public
function createInvoiceAction()
{
    $dataInvoice = $this->getRequest()->getPost();
    $xposUserId = '';
    $tillId = '';
    if (isset($dataInvoice['xpos_user_id']) && !empty($dataInvoice['xpos_user_id'])) {
        $xposUserId = $dataInvoice['xpos_user_id'];;
    }
    if (isset($dataInvoice['till_id']) && !empty($dataInvoice['till_id'])) {
        $tillId = $dataInvoice['till_id'];;
    }
    if ($dataInvoice['createInvoice'] == '1') {
        $order = Mage::getModel('sales/order')->load($dataInvoice['orderId']);
        $status = $order->getStatus();
        $state = $order->getState();
        $baseGrandTotal = $order->getBaseGrandTotal();
        $grandTotal = $order->getGrandTotal();
        $baseTotalPaid = $order->getBaseTotalPaid();
        $totalPaid = $order->getTotalPaid();

        if ($baseTotalPaid == 0) {
            $baseTotalPaid = $baseGrandTotal;
        }
        if ($totalPaid == 0) {
            $totalPaid = $grandTotal;
        }
        try {
            if (!$order->canInvoice()) {
                Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
            }

            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
            if (!$invoice->getTotalQty()) {
                Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
            }

            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
            $this->_castValueToOrder($invoice, $baseGrandTotal, $grandTotal, $baseTotalPaid, $totalPaid);
            $invoice->register();
            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder());

            $transactionSave->save();
        } catch (Mage_Core_Exception $e) {

        }
        $this->_castValueToOrder($order, $baseGrandTotal, $grandTotal, $baseTotalPaid, $totalPaid);
        $this->_changeProcessingStatus($order);
        $order->setXposUserId($xposUserId);
        $order->setTillId($tillId);
        $order->save();
        if ($order->getIncrementId()) {
            $payment = $method = $order->getPayment();
            $method = $payment->getMethod();
            $data_all = $order->getData();
            $data_transaction = array(
                'type'         => $method,
                'cash_in'      => $data_all['total_paid'],
                'cash_out'     => $data_all['total_paid'] - $data_all['grand_total'],
                'amount'       => $data_all['grand_total'],
                'till_id'      => $tillId,
                'warehouse_id' => '',
                'xpos_user_id' => $xposUserId,
                'order_id'     => $order->getIncrementId(),
            );
            if (isset($data_all['warehouse_id'])) {
                $data_transaction['warehouse_id'] = $data_all['warehouse_id'];
            }
            /*XPOS 2385 TODO: add split data to transaction*/
            if ($method == 'xpaymentMultiple') {
                $splitData = @unserialize($payment->getAdditionalData());
                $count = 0;
                $lastPaymentPos = count($splitData) - 1;
                foreach ($splitData as $methodCode => $cash) {
                    $data_transaction['payment_method'] = Mage::getStoreConfig('payment/' . $methodCode . '/title');
                    $data_transaction['type'] = $methodCode;
                    $data_transaction['cash_in'] = $cash;
                    $data_transaction['amount'] = $cash;
                    /*
                     * in case cash > GT has method add cash to drawer then out in last payment
                     * */
                    $data_transaction['cash_out'] = 0;
                    if ($count == $lastPaymentPos) {
                        $data_transaction['cash_out'] = $data_all['total_paid'] - $data_all['grand_total'];
                    }
                    Mage::getModel('xpos/transaction')->saveTransaction($data_transaction);
                    $count++;
                }

            } else {
                $data_transaction['payment_method'] = Mage::getStoreConfig('payment/' . $method . '/title');
                Mage::getModel('xpos/transaction')->saveTransaction($data_transaction);
            }
        }
    }

}

public
function completeAction()
{
    $this->_clear_current_order_cache();
    $result = array();
    try {
        $data = $this->getRequest()->getPost('order');
        $data_all = $this->getRequest()->getPost();

        $paymentData = $this->getRequest()->getPost('payment');
        $order = Mage::getModel('sales/order')->load($data_all['sm_order_id']);
        $storeId = $order->getStore()->getId();

        $baseCurrencyCode = Mage::app()->getStore($storeId)->getBaseCurrencyCode();
        $currentCurrencyCode = Mage::app()->getStore($storeId)->getCurrentCurrencyCode();
        Mage::dispatchEvent('x_pos_complete_order_before', array('order' => $order));

        $quote_data = $this->_getOrderCreateModel()->getQuote();

        $cash_in = $totalPaid = $this->getRequest()->getPost('cash-in');

        $totalRefunded = $this->getRequest()->getPost('balance');
        $creditStore = $quote_data->getData('customer_balance_amount_used');
        $giftCard = $quote_data->getData('gift_cards_amount');

        $order_discount = $order->getDiscountAmount();
//            $grandTotal = Mage::helper('directory')->currencyConvert($order->getBaseGrandTotal(), $baseCurrencyCode, $currentCurrencyCode);

        /*get rate for calculate price using 2 currency*/
        $helper = Mage::helper('xpos');

        $baseGT = $order->getBaseGrandTotal();
        $grandTotal = $order->getGrandTotal();
        $grandTotalPaid = Mage::app()->getStore()->roundPrice($helper->getConvertedValue($storeId, $cash_in));
//            Mage::helper('directory')->currencyConvert($cash_in, $currentCurrencyCode, $baseCurrencyCode );
        /*
         * TODO XPOS-3174 diff 0.01 unit
         * */
        if ($baseGT == $cash_in && $grandTotal != $grandTotalPaid) {
            $grandTotalPaid = $grandTotal;
        }

        /* Create Invoice & Shipment */
        $canShip = false;
        $itemCollection = $quote_data->getItemsCollection();
        $authorize_value = Mage::getStoreConfig('payment/authorizenet/payment_action');

        foreach ($itemCollection as $item) {
            $isVirtual = $item->getData('is_virtual');
            if (empty($isVirtual)) {
                $canShip = true;
            }
        }
        if ($canShip && $data_all['doshipment']) {
            $savedQtys = $this->_getItemQtys();
            $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);
            $shipment->register();

            Mage::getModel('core/resource_transaction')
                ->addObject($order)
                ->addObject($shipment)
                ->save();
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
            $this->_castValueToOrder($order, $baseGT, $grandTotal, $order->getBaseTotalPaid(), $order->getTotalPaid());
            $order->save();
        }

        $inforOrder = Mage::getSingleton('adminhtml/session')->getInfoOrder();
        $flagInforOder = false;
        if (!!$inforOrder) {
            $inforOrder['totalPaid'] = 0;
            $flagInforOder = true;
        }
        if ($data_all['doinvoice']) {
            if ($flagInforOder) $inforOrder['totalPaid'] = $cash_in;
            $savedQtys = $this->_getItemQtys();
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($savedQtys);
            $this->_castValueToOrder($invoice, $baseGT, $grandTotal, $grandTotalPaid, $cash_in);
            $invoice->setState(Mage_Sales_Model_Order_Invoice::STATE_PAID);
            $invoice->register();
            Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($order)
                    ->save();
            $this->_castValueToOrder($order, $baseGT, $grandTotal, $grandTotalPaid, $cash_in);
        }
        $this->_castValueToOrder($order, $baseGT, $grandTotal, $order->getBaseTotalPaid(), $order->getTotalPaid());
        /* Save order */
        $order->save();

        if (!!$inforOrder) {
            Mage::getSingleton('adminhtml/session')->setInfoOrder($inforOrder);
        }
//            SM_XPos_Model_Iayz::startTime();
        $defaultCustomerId = Mage::helper('xpos/configXPOS')->getDefaultCustomerId();
        $customerId = $order->getCustomerId();
        if ($defaultCustomerId != $customerId) {
            /**
             * Compare POST/SEND(from XPOS) address with customer's exist addresses
             */
            $remove_customer_address_fields = array(
                'entity_id',
                'entity_type_id',
                'attribute_set_id',
                'increment_id',
                'parent_id',
                'created_at',
                'updated_at',
                'is_active',
                'customer_id',
                'vat_id',
                'fax',
                'company',
                'suffix',
                'middlename',
                'prefix',
                //                    'region_id',
                //                    'region'


            );
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $customer_address_list = array();
            $customer_address_list['billing_address'] = array();
            $customer_address_list['shipping_address'] = array();

            $check_billing_address = $data['billing_address'];
            $check_billing_address['street'] = $data['billing_address']['street'][0];
            unset($data['billing_address'][0]);
//                unset($check_billing_address['region_id']);
//                unset($check_billing_address['region']);

            $check_shipping_address = $data['shipping_address'];
            $check_shipping_address['street'] = $data['shipping_address']['street'][0];
            unset($data['shipping_address'][0]);
//                unset($check_shipping_address['region_id']);
//                unset($check_shipping_address['region']);
            unset($check_billing_address['region']);
            unset($check_shipping_address['region']);
            $arrDiffShipp = array();
            $arrDiffBill = array();
            if (count($customer->getAddressesCollection()) > 0) {
                $countBillAdd = 0;
                $countShippAdd = 0;

                foreach ($customer->getAddressesCollection() as $address) {
                    $customer_address = $address->getData();
                    foreach ($remove_customer_address_fields as $remove_field) {
                        unset($customer_address[$remove_field]);
                    }

                    $check_exist_billing_address = array_diff_assoc($check_billing_address, $customer_address);
                    if (empty($check_exist_billing_address)) {//Different
                        $countBillAdd++;
                    }

                    $check_exist_shipping_address = array_diff_assoc($check_shipping_address, $customer_address);
                    if (empty($check_exist_shipping_address)) {//Different
                        $countShippAdd++;
                    }
                    array_push($arrDiffShipp, $check_exist_shipping_address);
                    array_push($arrDiffBill, $check_exist_billing_address);
//                        echo "<pre>";
//                        var_dump($check_billing_address);
//                        var_dump($customer_address);
//                        var_dump($check_exist_billing_address);
//
//                        var_dump($check_shipping_address);
//                        var_dump($customer_address);
//                        var_dump($check_exist_shipping_address);
//                        die;
                }
            }
            /**
             * Set/Save default shipping address.
             * If not exist address. This will add a new none, else it will remain.
             */
            if ($countShippAdd == 0) {
                $customAddress = Mage::getSingleton('customer/address');
                $customAddress->setData($data['shipping_address'])
                    ->setCustomerId($customerId)
                    ->setIsDefaultShipping('1');
                try {
                    $customAddress->setConfirmation(null);
                    $customAddress->save();
                } catch (Exception $ex) {
                }
//                    $quote->setBillingAddress(Mage::getSingleton('sales/quote_address')->importCustomerAddress($customAddress));
            }


            /**
             * Set/Save default shipping address
             */
            $diff = array();
            foreach ($data['shipping_address'] as $k => $detail) {
                if ($detail != $data['billing_address'][$k]) {
                    $diff[$k] = $detail;
                }
            }

            if ($countBillAdd == 0) {
                if (empty($diff)) {
                    $customAddress->setIsDefaultBilling('1')->save();
                } else {
                    $customAddress = Mage::getSingleton('customer/address');
                    $customAddress->setData($data['billing_address'])
                        ->setCustomerId($customerId)
                        ->setIsDefaultBilling('1');
                    try {
                        $customAddress->save();
                        $customAddress->setConfirmation(null);
                        $customAddress->save();
                    } catch (Exception $ex) {
                    }
                }
                //$quote->setBillingAddress(Mage::getSingleton('sales/quote_address')->importCustomerAddress($customAddress));
            }


            /**
             * Set/Save default shipping address
             */


            /*
             * END PROCESS AND CREATE BILLING, SHIPPING ADDRESS
             */
        }
//            $t = SM_XPos_Model_Iayz::timeExecution();

        if (isset($paymentData['method'])) {
            $payment_method = $paymentData['method'];
        } else {
            $payment_method = 'xpaymentMultiple';
        }

        if ($order->getIncrementId() && $data_all['doinvoice']) {
            $data_transaction = array(
                'type'         => $payment_method,
                'cash_in'      => $grandTotalPaid,
                'cash_out'     => $grandTotalPaid - $grandTotal,
                'amount'       => $grandTotal,
                'till_id'      => $data_all['till_id'],
                'warehouse_id' => $data_all['warehouse_id'],
                'xpos_user_id' => $data_all['xpos_user_id'],
                'order_id'     => $order->getIncrementId(),
            );
            /*XPOS 2385 TODO: add split data to transaction*/
            $payment = $method = $order->getPayment();
            $method = $payment->getMethod();
            if ($method == 'xpaymentMultiple') {
                $splitData = @unserialize($payment->getAdditionalData());
                $count = 0;
                $lastPaymentPos = count($splitData) - 1;
                foreach ($splitData as $methodCode => $cash) {
                    $data_transaction['payment_method'] = Mage::getStoreConfig('payment/' . $methodCode . '/title');
                    $data_transaction['type'] = $methodCode;
                    $data_transaction['cash_in'] = $cash;
                    $data_transaction['amount'] = $cash;
                    /*
                     * in case cash > GT has method add cash to drawer then out in last payment
                     * */
                    $data_transaction['cash_out'] = 0;
                    if ($count == $lastPaymentPos) {
                        $data_transaction['cash_out'] = $grandTotalPaid - $grandTotal;
                    }
                    Mage::getModel('xpos/transaction')->saveTransaction($data_transaction);
                    $count++;
                }

            } else {
                $data_transaction['payment_method'] = Mage::getStoreConfig('payment/' . $payment_method . '/title');
                Mage::getModel('xpos/transaction')->saveTransaction($data_transaction);
            }
        }

        $this->_getSession()->clear();
        /*send email to customer --------------------------------------------------*/
        if (Mage::helper('xpos/data')->isEmailConfirmationEnabled()) {

            /* Sending email | Send email */
            $doEmailReceipt = $this->getRequest()->getParam('doemailreceipt');
            $email = $this->getRequest()->getParam('emailreceipt');
            if (empty($email))
                $email = $this->getRequest()->getParam('tempemailreceipt');

            $isSendEmailCopy = true;
            if (!empty($doEmailReceipt) && !empty($email)) {
//                    try {
                $mailer = Mage::getModel('core/email_template_mailer');
                if ($data['account']['type'] == 'guest') { //$order->getCustomerIsGuest()
                    $templateId = Mage::getStoreConfig('xpos/receipt/guest_template', $storeId);
                    if ($templateId == 'xpos_receipt_guest_template' || empty($templateId)) {
                        //sales_email_invoice_guest_template
                        $templateId = Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
                    }
                    $customerName = $order->getBillingAddress()->getName();
                } else {
                    if ($data_all['doinvoice']) {
                        $templateId = Mage::getStoreConfig('xpos/receipt/customer_template', $storeId);
                        if ($templateId == 'xpos_receipt_customer_template' || empty($templateId)) { //Set default template
                            //sales_email_invoice_template
                            $templateId = Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_TEMPLATE, $storeId);
                        }
                        $customerName = $order->getCustomerName();
                    } else {
//                            $templateId = Mage::getStoreConfig('xpos/receipt/customer_template', $storeId);
//                            if ($templateId == 'xpos_receipt_customer_template' || empty($templateId)) { //Set default template
                        $templateId = Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_TEMPLATE, $storeId);
//                            }
                        $customerName = $order->getCustomerName();
                    }
                }

                $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                    ->setIsSecureMode(true);
                $paymentBlock->getMethod()->setStore($storeId);
                $paymentBlockHtml = $paymentBlock->toHtml();


                $emailInfo = Mage::getModel('core/email_info');

                $isSendOrderEmail = Mage::getStoreConfig('sales_email/order/enabled');
                $isSendInvoiceEmail = Mage::getStoreConfig('sales_email/invoice/enabled');
                if ($data_all['doinvoice'] && $isSendInvoiceEmail == 1) {
                    $emailInfo->addTo($email, $customerName);
                } else {
                    if ($isSendOrderEmail == 1) {
                        $emailInfo->addTo($email, $customerName);
                    }
                }
                //Send BCC.
                if (Mage::getStoreConfig('xpos/receipt/copy_method') == 'bcc') {
                    $isSendEmailCopy = false;
                    $copy_emails = Mage::getStoreConfig('xpos/receipt/sales_emails');
                    $copy_emails = explode(',', $copy_emails);
                    if (is_array($copy_emails) && count($copy_emails) > 0) {
                        foreach ($copy_emails as $copy_email) {
                            $copy_email = trim(strip_tags($copy_email));
                            //$mailer->addBcc($copy_email);
                            $emailInfo->addBcc($copy_email);
                        }
                    }
                }

                $mailer->addEmailInfo($emailInfo);

                //Set sender: sendersales
                $sender = Mage::getStoreConfig('xpos/receipt/sender');
                if (empty($sender)) {
                    //sales
                    $sender = Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_IDENTITY, $storeId);
                }

                $mailer->setTemplateId($templateId);
                $mailer->setSender($sender);
                $mailer->setStoreId($storeId);
                if ($data_all['doinvoice']) {
                    $mailer->setTemplateParams(array(
                            'order'        => $order,
                            'invoice'      => $invoice,
                            'comment'      => '',
                            'billing'      => $order->getBillingAddress(),
                            'payment_html' => $paymentBlockHtml,
                        )
                    );
                } else {
                    $mailer->setTemplateParams(array(
                            'order'        => $order,
                            'comment'      => '',
                            'billing'      => $order->getBillingAddress(),
                            'payment_html' => $paymentBlockHtml,
                        )
                    );
                }
                $mailer->send();
                /****Change status email send***/
                $order->setData('email_sent', 1)->save();
                if (isset($invoice))
                    $invoice->setData('email_sent', 1)->save();
            }

            if (($sale_emails = Mage::getStoreConfig('xpos/receipt/sales_emails') != '') && ($isSendEmailCopy == true) && !empty($email)) {
                $sale_emails = Mage::getStoreConfig('xpos/receipt/sales_emails');
                $sale_emails = explode(',', $sale_emails);

                if (is_array($sale_emails) && count($sale_emails) > 0) {
                    //Send email to Sales team
                    foreach ($sale_emails as $sale_email) {
                        $sale_email = trim(strip_tags($sale_email));
                        if (filter_var($sale_email, FILTER_VALIDATE_EMAIL)) {
                            try {
                                $mailer = Mage::getModel('core/email_template_mailer');
                                $storeId = $order->getStore()->getId();
                                $templateId = Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_TEMPLATE, $storeId);
                                $customerName = $sale_email;

                                $mailer->setTemplateId($templateId);

                                $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                                    ->setIsSecureMode(true);
                                $paymentBlock->getMethod()->setStore($storeId);
                                $paymentBlockHtml = $paymentBlock->toHtml();


                                $emailInfo = Mage::getModel('core/email_info');
                                $emailInfo->addTo($sale_email, $customerName);

                                $mailer->addEmailInfo($emailInfo);

                                //Set sender
                                $sender = Mage::getStoreConfig('xpos/receipt/sender');
                                if (empty($sender)) {
                                    $sender = Mage::getStoreConfig(Mage_Sales_Model_Order_Invoice::XML_PATH_EMAIL_IDENTITY, $storeId);
                                }
                                $mailer->setSender($sender);
                                $mailer->setStoreId($storeId);

                                $mailer->setTemplateParams(array(
                                        'order'        => $order,
                                        //                                            'invoice' => $invoice,
                                        'comment'      => '',
                                        'billing'      => $order->getBillingAddress(),
                                        'payment_html' => $paymentBlockHtml,
                                    )
                                );
                                if (Mage::getStoreConfig('xpos/receipt/copy_method') != 'bcc')
                                    $mailer->send();

                            } catch (Exception $e) {
                                Mage::logException($e);
                                $this->_getSession()->addError($this->__('Unable to send the invoice email to ' . $sale_email));
                            }
                        }
                    }

                }
            }


        }

        //delete customer if customer is guest
        if ($data['account']['type'] == 'guest_') {
            $customer = Mage::getModel('customer/customer')->load($order->getData('customer_id'));
            $customer->delete();
            //Mage::getSingleton('core/session')->setCustomertype($data['account']['type']);
        }

        //Redirect to Paypal Here App
        if ($paymentData['method'] == 'here') {
            $href = Mage::helper("adminhtml")->getUrl('*/xpos/paypalhere', array('order_id' => $order->getId()));
            Mage::getSingleton('core/session')->setOrderIncrementId($order->getIncrementId());
            Mage::getSingleton('core/session')->setPaypalhereUrl($href);
            Mage::log("PPhereURLCotroller=" . $href);
            Mage::getSingleton('core/session')->setReturnUrl(Mage::helper('adminhtml')->getUrl('*/xpos/index'));
            //Redirect to paypalhere redirect page
            $this->_getSession()->addSuccess("Order #" . $order->getIncrementId() . " successfully created.");
            $result['url'] = Mage::helper('adminhtml')->getUrl("*/xpos/paypalhere", array('order_id' => $order->getId()));
        } else {
            if ($data_all['doinvoice']) {
                //$href = Mage::helper("adminhtml")->getUrl('*/xpos/print', array('order_id' => $order->getId()));
                //$doPrintReceipt = $this->getRequest()->getPost('doprintreceipt');
                //if (!empty($doPrintReceipt)) {
                //    Mage::getSingleton('core/session')->setPrintUrl($href);
                //}
            }
            $this->_getSession()->addSuccess("Order #" . $order->getIncrementId() . " successfully created.");
            $result['url'] = '*/xpos/index';
//                $this->removeOldOrderAction();
        }

        //Redirect to EPay
        if ($paymentData['method'] == 'epay_standard') {
            $href = Mage::getUrl('epay/standard/redirect', array('order_id' => $order->getId()));
            Mage::getSingleton('core/session')->setOrderIncrementId($order->getIncrementId());
            //Mage::getSingleton('core/session')->setPaypalhereUrl($href);
            Mage::log("PPhereURLCotroller=" . $href);
            Mage::getSingleton('core/session')->setReturnUrl(Mage::helper('adminhtml')->getUrl('*/xpos/index'));
            $quoteId = $quote_data->getId();
            $orderId = $order->getIncrementId();
            $result['url'] = Mage::getUrl("epay/standard/redirect", array('order_id' => $orderId, 'quote_id' => $quoteId));
        }
        $result['status'] = 'ok';
        $result['url'] = $this->getUrl('*/*/');
    } catch (Mage_Payment_Model_Info_Exception $e) {
        $this->_getOrderCreateModel()->saveQuote();
        $message = $e->getMessage();
        if (!empty($message)) {
            $this->_getSession()->addError($message);
        }
    } catch (Mage_Core_Exception $e) {
        $message = $e->getMessage();
        if (!empty($message)) {
            $this->_getSession()->addError($message);
        }
    } catch (Exception $e) {
        $this->_getSession()->addException($e, $this->__('Order saving error: %s', $e->getMessage()));
    }
    if (!isset($result['status'])) {
        $result['status'] = 'error';
        $result['url'] = $this->getUrl('*/*/');
    }
    $this->getResponse()->setBody(json_encode($result));
}

public
function removeOldOrderAction()
{
    try {
        $quote = $this->_getOrderCreateModel()->getQuote();
//            $storeInConfig = Mage::helper('xpos/product')->getCurrentSessionStoreId();
        $storeId = Mage::helper('xpos/product')->getCurrentSessionStoreId();
        $quote->setStoreId($storeId);
        $this->_getSession()->setStoreId((int)$storeId);
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $item) {
            $quote->removeItem($item->getId());
        }
        $quote->save();
        die();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

public
function completeOfflineAction()
{

    $this->_clear_current_order_cache();
    $this->_getSession()->clear();

    try {
        $request = $this->getRequest()->getParams();
        $action = $request['action'];
        $data = $request['order'];
        $data['shipping_method'] = "xpayment_pickup_shipping_xpayment_pickup_shipping";
        $data['account']['email'] = $request['customer_email'];
        //set Session Customer
        $this->_getSession()->setCustomerId((int)$data['customer_id']);
        $quote = $this->_getOrderCreateModel()->getQuote();
        $customer = Mage::getModel('customer/customer')->load($data['customer_id']);
        $quote->assignCustomer($customer);
        //Set StoreId
        $storeId = Mage::helper('xpos/product')->getCurrentSessionStoreId();
        $quote->setStoreId($storeId);
        $this->_getSession()->setStoreId((int)$storeId);
        //set BillingAdd/Shipping Add to Quote
        unset($data['billing_addressing']['street']);
        $orderBillingAdd = $data['billing_address'];
        $orderShippingAdd = $data['shipping_address'];
        $data['billing_address']['street'] = Mage::getModel('xpos/guest')->getStreet($orderBillingAdd['street']);
        unset($data['shipping_address']['street']);
        $data['shipping_address']['street'] = Mage::getModel('xpos/guest')->getStreet($orderShippingAdd['street']);
        $quote->getBillingAddress()->addData($data['billing_address']);
        $quote->getShippingAddress()->addData($data['shipping_address']);
        $quote->save();

        //remove old Item in Quote and add current item to quote
        $quoteItems = $quote->getAllItems();
        foreach ($quoteItems as $item) {
            $quote->removeItem($item->getId());
        }
        $quoteItems = $request['item'];
        $items = array();
        foreach ($quoteItems as $item => $itemdetails) {
            if (@$itemdetails['qty'] != 0)
                $items[$item] = $itemdetails;
        }
        $items = $this->_processFiles($items);
        $this->_getOrderCreateModel()->addProducts($items);
        $this->_getOrderCreateModel()->updateCustomPrice($quoteItems);

        $this->_setShipping($data['shipping_method']);
        $quote->getShippingAddress()
            ->setShippingMethod('xpayment_pickup_shipping_xpayment_pickup_shipping')
            ->setCollectShippingRates(true)
            ->collectTotals();
        $this->_getOrderCreateModel()->setPaymentMethod($request['payment']['method']);
        $order = $this->_getOrderCreateModel()
            ->setIsValidate(true)
            ->createOrder();
        $order->setData('xpos', 1);
        $order->setData('till_id',$request['till_id']);
        /* Create Invoice & Shipment */
        $savedQtys = $this->_getItemQtys();
        if ($action == '2' || $action == '1') {
            $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($savedQtys);
            $shipment->register();

            Mage::getModel('core/resource_transaction')
                ->addObject($order)
                ->addObject($shipment)
                ->save();
            $this->_changeProcessingStatus($order);
        }
        if ($action == '3' || $action == '1') {
            /* Complete the order */
            $cash_in = $totalPaid = $request['cash-in'];
            $totalRefunded = $request['balance'];
            $grand_total = $order->getGrandTotal();
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($savedQtys);
            $invoice->register();
            Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($order)->save();

            $this->_changeProcessingStatus($order);
            $payment_method = $request['payment']['method'];
            if (($order->getId())) {
                $data_transaction = array(
                    'type'         => $payment_method,
                    'cash_in'      => $cash_in,
                    'cash_out'     => $totalRefunded,
                    'amount'       => $grand_total,
                    'till_id'      => $request['till_id'],
                    'warehouse_id' => $request['warehouse_id'],
                    'xpos_user_id' => $request['xpos_user_id'],
                    'order_id'     => $order->getIncrementId(),
                );
                $data_transaction['payment_method'] = Mage::getStoreConfig('payment/' . $payment_method . '/title');
                Mage::getModel('xpos/transaction')->saveTransaction($data_transaction);
            }
        }
        $order->save();
        $this->_getSession()->clear();

        $result = array(
            'status'   => 'success',
            'msg'      => $order->getIncrementId(),
            'id'       => $order->getId(),
            'printurl' => Mage::helper("adminhtml")->getUrl('*/xPos/print', array('order_id' => $order->getId())),
        );


    } catch (Mage_Payment_Model_Info_Exception $e) {
        $this->_getOrderCreateModel()->saveQuote();
        $result['status'] = 'error';
        $result['msg'] = $e->getMessage();
    } catch (Mage_Core_Exception $e) {
        $result['status'] = 'error';
        $result['msg'] = $e->getMessage();
    } catch (Exception $e) {
        $result['status'] = 'error';
        $result['msg'] = $e->getMessage();
    }
    $this->getSession()->setData(SM_XPos_Model_Discount_Observer::XPOS_OFFLINE_ORDER, 0);
    echo json_encode($result);
    die;
}

public
function saveAction()
{
    $this->_clear_current_order_cache();
    try {
        $orderData = $this->getRequest()->getPost('order');
        $amountShipping = 0;
        if (isset($orderData['shipping_method']) && !empty($orderData['shipping_method'])) {
            $shippingMethod = $orderData['shipping_method'];
            if ($shippingMethod == 'xpayment_pickup_shipping_xpayment_pickup_shipping') {

                $pickUpshipmentData = $this->getRequest()->getPost('shipment');
                $amountShipping = $pickUpshipmentData['xpayment_pickup_shipping_xpayment_pickup_shipping'];
                $this->registerCustomShippingAmount($amountShipping);
            }

        } else {
            $shippingMethod = Mage::getStoreConfig('xpos/checkout/default_shipping');
        }
        $this->_setShipping($shippingMethod);
        $data = $this->_processData('save');
        // if($amountShipping > 0){
        Mage::getSingleton('adminhtml/sales_order_create')->collectShippingRates();
        // }
        $paymentData = $this->getRequest()->getPost('payment');
        if ($paymentData && isset($paymentData['method']) && (in_array($paymentData['method'], array('ccsave', 'authorizenet')))) {
            $this->_getOrderCreateModel()->setPaymentData($paymentData);
        }

        $order = $this->_getOrderCreateModel()
            ->setIsValidate(true)
            ->importPostData($data)
            ->createOrder();
        Mage::dispatchEvent('x_pos_complete_order_before', array('order' => $order));
        $order->setState(Mage_Sales_Model_Order::STATE_NEW, true);
        $order->setTotalPaid(0)->save();
        $this->_getSession()->clear();

        Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The order has been created.'));
        $this->_redirect('*/*/index');
    } catch (Mage_Payment_Model_Info_Exception $e) {
        $this->_getOrderCreateModel()->saveQuote();
        $message = $e->getMessage();
        if (!empty($message)) {
            $this->_getSession()->addError($message);
        }
        $this->_redirect('*/*/');
    } catch (Mage_Core_Exception $e) {
        $message = $e->getMessage();
        if (!empty($message)) {
            $this->_getSession()->addError($message);
        }
        $this->_redirect('*/*/');
    } catch (Exception $e) {
        $this->_getSession()->addException($e, $this->__('Order saving error: %s', $e->getMessage()));
        $this->_redirect('*/*/');
    }
}

public
function printinvoiceAction()
{
    if ($this->getRequest()->getParam('preview') != null) {
        $receiptPreviewingMode = Mage::getSingleton('xpos/modes');
        $receiptPreviewingMode->setIsReceiptPreviewingMode(true);
        $this->_title('Preview Receipt');
    } else {
        $order_id = $this->getRequest()->getParam('order_id', 1);
        $this->_title("Invoice No." . $order_id);
    }

    $this->loadLayout();
    if ($giftReceipt = $this->getRequest()->getParam('printGift') == 1)
        $this->getLayout()->getBlock('root')->assign('printGift', true);
    else
        $this->getLayout()->getBlock('root')->assign('printGift', false);

    $this->renderLayout();
}

public
function printcreditmemoAction()
{
//        $order_id = $this->getRequest()->getParam('order_id', 1);
//        $this->_title("Invoice No." . $order_id);
//        $this->loadLayout();
//        $this->renderLayout();

    $creditmemo = $this->_initCreditmemo();
    if ($creditmemo) {
        if ($creditmemo->getInvoice()) {
            $this->_title($this->__("View Memo for #%s", $creditmemo->getInvoice()->getIncrementId()));
        } else {
            $this->_title($this->__("View Memo"));
        }

        $this->loadLayout();
        $this->renderLayout();
    } else {
        echo 'Error when processing!!!!';
    }
}


public
function clearAction()
{
    $this->_clear_current_order_cache();
    $this->_getSession()->clear();
   $this->_clearAll();


    $this->_redirect('*/*/');
}

public
function loadBlockAction()
{
    SM_XPos_Adminhtml_XposController::$_callFromControllerXpos = true;
    $fBlock = $this->getRequest()->getParam('block');
    $checkFirstLoad4Block = $this->checkFirstLoad4Block($fBlock);
    if (!Mage::getModel('xpos/integrate')->isIntegrateRp() && !Mage::getModel('xpos/integrate')->isIntegrateWithWebtexGiftCard()) {
        if (!$checkFirstLoad4Block['status'] || $fBlock == 'card_validation') {
            return;
        }
    }
//        $block = $this->checkFirstLoad4Block($fBlock)['blocks'];
    $this->checkFirstLoad4Block($fBlock);
    /*items,shipping_method,billing_method,totals*/
    $order_id = $this->getRequest()->getParam('orderId');
    if ($order_id != NULL) {
        Mage::getSingleton('adminhtml/session')->setOrderViewDetail($order_id);
        Mage::getSingleton('adminhtml/session')->setInfoOrder(null);
    }

    $request = $this->getRequest();
    try {
        $this->_initSession()
            ->_processData();

    } catch (Mage_Core_Exception $e) {
        $this->_reloadQuote();
        $this->_getSession()->addError($e->getMessage());

    } catch (Exception $e) {
        $this->_reloadQuote();
        $this->_getSession()->addException($e, $e->getMessage());
    }

    $asJson = $request->getParam('json');
    $block = $request->getParam('block');

    if ($block == 'sales_creditmemo_create') {
        $creditmemo = $this->_initCreditmemo();
    }

    $update = $this->getLayout()->getUpdate();
    if ($asJson) {
        $update->addHandle('adminhtml_xpos_load_block_json');
    } else {
        $update->addHandle('adminhtml_xpos_load_block_plain');
    }

    if ($block) {
        $blocks = explode(',', $block);
        if ($asJson && !in_array('message', $blocks)) {
            $blocks[] = 'message';
        }

        foreach ($blocks as $block) {
            $update->addHandle('adminhtml_xpos_load_block_' . $block);
        }
    }
    $this->loadLayoutUpdates()->generateLayoutXml()->generateLayoutBlocks();
    $result = $this->getLayout()->getBlock('content')->toHtml();
    SM_XPos_Adminhtml_XposController::$_callFromControllerXpos = false;
    if ($request->getParam('as_js_varname')) {
        Mage::getSingleton('adminhtml/session')->setUpdateResult($result);
        $this->_redirect('*/*/showUpdateResult');
    } else {
        $this->getResponse()->setBody($result);
    }
}

public
function addCommentAction()
{
    if ($order = $this->_initOrder()) {
        try {
            $response = false;
            $data = $this->getRequest()->getPost('history');
            $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
            $visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;

            $order->addStatusHistoryComment($data['comment'], $data['status'])
                ->setIsVisibleOnFront($visible)
                ->setIsCustomerNotified($notify);

            $comment = trim(strip_tags($data['comment']));

            $order->save();
            $order->sendOrderUpdateEmail($notify, $comment);

        } catch (Mage_Core_Exception $e) {
            $response = array(
                'error'   => true,
                'message' => $e->getMessage(),
            );
        } catch (Exception $e) {
            $response = array(
                'error'   => true,
                'message' => $this->__('Cannot add order history.'),
            );
        }
        if (is_array($response)) {
            $response = Mage::helper('core')->jsonEncode($response);
            $this->getResponse()->setBody($response);
        } else {
            $block = $this->getLayout()->createBlock("xpos/adminhtml_override_history")
                ->setTemplate("sm/xpos/override/history.phtml")->toHtml();

            $result = array('block' => $block);
            $this->getResponse()
                ->clearHeaders()
                ->setHeader('Content-Type', 'application/json')
                ->setBody(Mage::helper('core')->jsonEncode($result));

            return $result;
        }
    }
}

public
function loginAction()
{
    $xpos_user_username = $this->getRequest()->getParam('xpos_user_username');
    $xpos_user_password = $this->getRequest()->getParam('xpos_user_password');
    $xpos_store_id = $this->getRequest()->getParam('xpos_store_id');

    $data_cashier = Mage::getModel('xpos/user')->getCollection();

    $user_admin = Mage::getSingleton('admin/session');
    $user_admin_id = $user_admin->getUser()->getUserId();

    $data_cashier->addFieldToFilter('username', array('eq' => $xpos_user_username));
    $data_cashier->addFieldToFilter('password', array('eq' => $xpos_user_password));
    $data_cashier->addFieldToFilter('user_id', array('eq' => $user_admin_id));
    $data_cashier->addFieldToFilter('is_active', array('eq' => 1));
    $row = $data_cashier->getFirstItem();
    if (!!count($row->getData())) {
        if (!in_array($xpos_store_id, json_decode($row->getStoreId(), true)) && !in_array(0, json_decode($row->getStoreId(), true)))
            $cashier['is_wrong_store'] = '1';
        else {
            $cashier['xpos_user_id'] = $row->getXposUserId();
            $cashier['username'] = $row->getUsername();
            $cashier['email'] = $row->getEmail();
            $cashier['firstname'] = $row->getFirstname();
            $cashier['lastname'] = $row->getLastname();
            $cashier['type'] = $row->getType();
            Mage::register('is_user_limited', $row->getType());
            Mage::getSingleton('adminhtml/session')->setData('is_user_limited', $row->getType());
        }
        return $this->getResponse()->setBody(json_encode($cashier));
    } else
        return null;
}

public
function customerSearchAction()
{
    $items = array();

    $start = $this->getRequest()->getParam('start', 1);
    $limit = $this->getRequest()->getParam('limit', 1000);
    $query = $this->getRequest()->getParam('query', '');

    //$searchInstance = new SM_XPos_Model_Search_Customer;
    $searchInstance = Mage::getModel('xpos/search_customer');

    $results = $searchInstance->setStart($start)
        ->setLimit($limit)
        ->setQuery($query)
        ->load()
        ->getResults();

    $items = array_merge_recursive($items, $results);

    $totalCount = sizeof($items);

    $block = $this->getLayout()->createBlock('adminhtml/template')
        ->setTemplate('sm/xpos/index/customer/autocomplete.phtml')
        ->assign('items', $items);
    $this->getResponse()->setBody($block->toHtml());
}

public
function currentbalanceAction($type = 'json')
{
    $tillId = $this->getRequest()->getParam('till_id');
    $balance = Mage::getModel('xpos/transaction')->currentBalance($tillId);

    if ($type == 'json')
        return $this->getResponse()->setBody(json_encode($balance));
}

public
function newTransactionAction()
{
    $amount = $this->getRequest()->getParam('amount');
    $note = $this->getRequest()->getParam('note');
    $note = trim(strip_tags($note));
    $type = $this->getRequest()->getParam('type');
    $till_id = $this->getRequest()->getParam('till_id');
    $result = false;
    if (
        is_numeric($amount) &&
        $amount >= 0 &&
        in_array($type, array(
                'in',
                'out')
        )
    ) {

        $data = array(
            'amount'  => $amount,
            'type'    => $type,
            'till_id' => $till_id,
            'note'    => $note,
        );
        $result = Mage::getModel('xpos/transaction')->saveTransaction($data);
    }

    if ($result == false) {
        $result = array(
            'msg'   => 'Can NOT save this transaction. Please recheck the input value or contact with your administrator',
            'error' => true);
    }

    $this->getResponse()->setBody(json_encode($result));


}

public
function countAllCustomersAction()
{
    $number = Mage::getModel('xpos/search_customer')->getCountAll();
    $result = json_encode(array('number' => $number));
    $this->getResponse()->setBody($result);
}

public
function customerLoadAction()
{
    $limit = $this->getRequest()->getParam('limit', 10);
    $page = $this->getRequest()->getParam('page', 1);
    $items = array();

    //$searchInstance = new SM_XPos_Model_Search_Customer;
    $searchInstance = Mage::getModel('xpos/search_customer');
    $results = $searchInstance
        ->loadAll($limit, $page);

    if ($results)
        $items = $results->getResults();

    $this->getResponse()->setBody(json_encode($items));
}

public
function updateQtyAction()
{
    try {
        $creditmemo = $this->_initCreditmemo(true);
        $this->loadLayout();
        $response = $this->getLayout()->getBlock('order_items')->toHtml();
    } catch (Mage_Core_Exception $e) {
        $response = array(
            'error'   => true,
            'message' => $e->getMessage(),
        );
        $response = Mage::helper('core')->jsonEncode($response);
    } catch (Exception $e) {
        $response = array(
            'error'   => true,
            'message' => $this->__('Cannot update the item\'s quantity.'),
        );
        $response = Mage::helper('core')->jsonEncode($response);
    }
    $this->getResponse()->setBody($response);
}

public
function getSavedOrderAction()
{
    $isXman = 0;
    if (Mage::getStoreConfig('xmanager/general/enabled') == 1) {
        /*xmanager has been installed*/
        $isXman = 1;
    }
    if ($isXman == 1) {
        $per = Mage::getModel('xmanager/permission');
        if ($per->getShareCustomer() == 1 || $per->getPermission() == '0' || $per->getModuleStatus() == 0) {
            $collection = Mage::getResourceModel('sales/order_collection');
            $collection->addFieldToFilter('status', 'pending');
            $collection->addFieldToFilter('xpos', 1);
            echo $collection->getSize();
            die;
        } else {
            $allow = $per->getAllowAfterAss();
            $ass = $per->getAssigned();
            $isAss = '0';
            foreach ($ass as $as) {
                if ($as != '0') {
                    $isAss = '1';
                }
            }
            if ($allow == '0' && $isAss != '0') {
                echo '';
            }
            $arrAdminId = array();
            $currentId = $per->getCurrentAdmin();
            $currentId = $currentId['id'];

            $arrAdminId[] = $currentId;
            foreach ($per->getIdReceive() as $id) {
                $arrAdminId[] = $id;
            }
            $collection = Mage::getResourceModel('sales/order_collection');
            $collection->addFieldToFilter('status', 'pending');
            $collection->addFieldToFilter('xpos', 1);
            $collection->addAttributeToSelect('admin_id_create')
                ->addAttributeToFilter('admin_id_create', array('in' => $arrAdminId));
            echo $collection->getSize();
            die;
        }
    } else {
        $collection = Mage::getResourceModel('sales/order_collection');
        $collection->addFieldToFilter('status', 'pending');
        $collection->addFieldToFilter('xpos', 1);
        echo $collection->getSize();
        die;
    }

}

public
function checkNetworkAction()
{
    die("ok");
}

public
function openCashTransferAction()
{
    $staff_id = Mage::getSingleton('adminhtml/session')->getData('is_user_limited');
    $user = Mage::getModel('xpos/user')->load($staff_id);

    $staff_name = $user->getData('firstname') . ' ' . $user->getData('lastname');
    $now_time = Mage::getModel('core/date')->timestamp(time());
    echo "### " . date('d/m/Y H:i ', $now_time) . " Manual Opening of Cash Drawer " . "- " . $staff_name . " ###";

}

public
function createNewCustomerAction()
{
    $postData = $this->getRequest()->getParams();
    $email = $postData['email'];
    $firstName = $postData['firstname'];
    $lastname = $postData['lastname'];
    $newsletter = $postData['newsletter'];
    $billing_street = $postData['billing_street'];
    $billing_country_id = $postData['billing_country_id'];
    $billing_city = $postData['billing_city'];
    $billing_region = $postData['billing_region'];
    $billing_region_id = $postData['billing_region_id'];
    $billing_telephone = $postData['billing_telephone'];
    $shipping_street = $postData['shipping_street'];
    $shipping_country_id = $postData['shipping_country_id'];
    $shipping_city = $postData['shipping_city'];
    $shipping_region_id = $postData['shipping_region_id'];
    $shipping_region = $postData['shipping_region'];
    $shipping_postcode = $postData['shipping_postcode'];
    $shipping_telephone = $postData['shipping_telephone'];

    $customer = Mage::getModel('customer/customer');
    $billingAddress = array(
        'firstname'  => $firstName,
        'lastname'   => $lastname,
        'street'     => $billing_street,
        'city'       => $billing_city,
        'country_id' => $billing_country_id,
        'region'     => $billing_region,
        'region_id'  => $billing_region_id,
        'postcode'   => $shipping_postcode,
        'telephone'  => $billing_telephone,
    );
    $shippingAddress = array(
        'firstname'  => $firstName,
        'lastname'   => $lastname,
        'street'     => $shipping_street,
        'city'       => $shipping_city,
        'country_id' => $shipping_country_id,
        'region'     => $shipping_region,
        'region_id'  => $shipping_region_id,
        'postcode'   => $shipping_postcode,
        'telephone'  => $shipping_telephone,
    );
//        $storeInConfig = Mage::helper('xpos/product')->getCurrentSessionStoreId();
    $storeId = Mage::helper('xpos/product')->getCurrentSessionStoreId();
    $store = Mage::getModel('core/store')->load($storeId);
    $website = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
    $customer->setWebsiteId($website);
    $email_customer = $email;
    $customer->loadByEmail($email_customer);
    if (!$customer->getId() && $email_customer != '') {


        $pass = 'auto' . microtime(true);
        $customer->setStore($store);
        $customer->setEmail($email_customer);
        $customer->setFirstname($firstName);
        $customer->setLastname($lastname);
        $customer->setPassword($pass);
        $isXman = 0;
        if (Mage::getStoreConfig('xmanager/general/enabled') == 1) {
            /*xmanager has been installed*/
            $isXman = 1;
        }
        if ($isXman == 1) {
            $per = Mage::getModel('xmanager/permission');
            $currentAdmin = $per->getCurrentAdmin();
            $adminId = $currentAdmin['id'];
            $customer->setData('admin_id', $adminId);
        }
        $customer->save();
        // set Default Billing And Shipping:
        $add = Mage::getModel('customer/address');
        $add->setData($billingAddress)
            ->setCustomerId($customer->getId())
            ->setIsDefaultBilling('1')
            ->setSaveInAddressBook('1');
        if (count(array_diff($billingAddress, $shippingAddress)) > 0) {
            $add->save();
            $add->setData($shippingAddress)
                ->setCustomerId($customer->getId())
                ->setIsDefaultShipping('1')
                ->setSaveInAddressBook('1');
            $add->save();
        } else {
            $add->setIsDefaultShipping('1')->save();
        }
        $customer->setForceConfirmed(true);
        $sendPassToEmail = true;
        $customer->setPassword($customer->generatePassword());
        $customer->save();
        // Send welcome email
        if ($customer->getWebsiteId() || $sendPassToEmail) {
            $storeId = $customer->getSendemailStoreId();
            $customer->sendNewAccountEmail('registered', '', $storeId);
        }

        /*subscribing-a-customer*/
        if ($newsletter == 1) {
            $subscriber = Mage::getModel('newsletter/subscriber')->loadByEmail($email_customer);
            if ($subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED &&
                $subscriber->getStatus() != Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED
            ) {
                $subscriber->setStatus(Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED);
                $subscriber->setSubscriberEmail($email);
                $subscriber->setSubscriberConfirmCode($subscriber->RandomSequence());
            }
            $subscriber->setStoreId($store->getId());
            $subscriber->setCustomerId($customer->getId());
            try {
                $subscriber->save();
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
        $newPassword = $customer->generatePassword();
        $customer->changePassword($newPassword);
        $customer->sendPasswordReminderEmail();
        $newCustomerId = $customer->getId();
        $result = array(
            'customerId' => $newCustomerId,
            'error'      => false);
        $result = Mage::helper('core')->jsonEncode($result);
        die($result);
    } else {
        /* email exist*/
        $result = array(
            'customerId' => '0',
            'error'      => true);
        $result = Mage::helper('core')->jsonEncode($result);
        die($result);
    }
}

public
function getTotalsQuoteAction()
{
    $session = Mage::getSingleton('adminhtml/session_quote');
    $quote = $session->getQuote();
    $valueOfTotal = $this->getRequest()->getParam('value');
    $defaultShipping = $this->getRequest()->getParam('shippingMethod');
    $cost = $this->getRequest()->getParam('xpos_shipping_cost');
    $this->registerCustomShippingAmount($cost);
    $value = 0;
    $data['shipping_method'] = $defaultShipping;
    //saved shipment to session
    Mage::getSingleton('adminhtml/session')->setSelectedShipmentMethod($data['shipping_method']);
    Mage::getSingleton('adminhtml/sales_order_create')->importPostData($data)->collectShippingRates();
    $totals = $quote->getTotals();
    if (!empty($totals[$valueOfTotal]) && $totals[$valueOfTotal] instanceof Varien_Object) {
        $value = $totals[$valueOfTotal]->getData('value');
    }
    $taxShip = $quote->getShippingAddress()->getData('tax_amount');
    $taxBill = $quote->getBillingAddress()->getData('tax_amount');
    $discountTotal = 0;
    foreach ($quote->getAllItems() as $item) {
        $discountTotal += $item->getDiscountAmount();
    }
    $result = array(
        'discount' => $discountTotal,
        'data'     => $value,
        'tax'      => $taxBill + $taxShip,
    );
    $this->getResponse()->setBody(json_encode($result));
}

public
function getSession()
{
    return Mage::getSingleton('adminhtml/session_quote');
}

public
function getListCustomerAction()
{
    $requestData = $this->getRequest()->getParams();
    $model = Mage::getModel('xpos/guest');
    $result = $model->getListCustomerForSelectInSetting($requestData);
    $result = Mage::helper('core')->jsonEncode($result);
    $this->getResponse()->setBody($result);
}

public
function createAction()
{
    $name = $this->getRequest()->getParam('sku');
    $num = $this->getRequest()->getParam('num');
    if ($name == null || $num == null) {
        die('Phai co sku(name) va so luong(num)');
    }
    Mage::helper('xpos/product')->createDummyProduct($name, $num);
}

public
function checkFirstLoad4Block($block)
{
    if (in_array($block, array('items,shipping_method,billing_method,totals', 'items,shipping_method,billing_method,totals,coupons'))) {
        SM_XPos_Adminhtml_XposController::$_firstLoad4B = true;
        Mage::getSingleton('adminhtml/session')->setFirstLoad4Block(true);

        return array(
            'status' => true,
            'blocks' => 'items,billing_method,totals,shipping_method',
        );
    }
    if (in_array($block, array('shipping_method,totals', 'shipping_method,totals,coupons')) && Mage::getSingleton('adminhtml/session')->getFirstLoad4Block() == true) {
        Mage::getSingleton('adminhtml/session')->setFirstLoad4Block(false);

        return array(
            'status' => false,
            'blocks' => $block,
        );
    }

    return array(
        'status' => true,
        'blocks' => $block,
    );
}


public
function getFirstLoad4Block()
{
    return Mage::getSingleton('adminhtml/session')->getFirstLoad4Block();
}

public
function flushXPosCacheAction()
{
    Mage::dispatchEvent('adminhtml_cache_flush_xpos');
    Mage::app()->getCache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('xpos_cache'));
    $this->_getSession()->addSuccess(Mage::helper('adminhtml')->__("The cache X-POS has been flushed."));
    $this->_redirect('*/*');
}

public
function saveDataOfflineOrderAction()
{
    $dataAll = $this->getRequest()->getParams();
    $postData = array(
        'orderid'            => null,
        'customer_id'        => $dataAll['order']['customer_id'],
        'grand_total'        => $dataAll['grand_total'],
        'total_paid'         => $dataAll['cash-in'],
        'shipping_method'    => null,
        'payment_method'     => $dataAll['payment']['method'],
        'is_create_invoice'  => $dataAll['doinvoice'],
        'is_create_shipment' => $dataAll['doshipment'],
        'items'              => json_encode($dataAll['item']),
        'postData'           => json_encode($dataAll),
    );

    Mage::getModel('xposoffline/xposoffline')->addDataFromOrder($postData);
    $result = array(
        'status'   => 'success',
        'msg'      => '',
        'printurl' => null,
    );
    die(json_encode($result));
}


private
function _castValueToOrder($order = null, $baseGrandTotal = 0, $grandTotal = 0, $baseTotalPaid = 0, $totalPaid = 0)
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

private
function _setShipping($shippingMethod)
{
    Mage::unregister('pos_shipping_method');
    Mage::register('pos_shipping_method', $shippingMethod);
    $this->_getOrderCreateModel()->setShippingMethod($shippingMethod);
}

public
function checkCustomerAction()
{
    $data = $this->getRequest()->getParams();
    $customerId = Mage::getStoreConfig('xpos/checkout/default_customer_id', $data['storeId']);
    $cus = Mage::getModel('customer/customer')->load($customerId);
    $respon = array('success' => true);
    $store = $cus->getData('store_id');
    $web = $cus->getData('website_id');
    $currentWeb = '';
    foreach (Mage::app()->getWebsites() as $website) {
        foreach ($website->getGroups() as $group) {
            $stores = $group->getStores();
            foreach ($stores as $store) {
                if ($store->getId() == $store) {
                    $web = $website->getId();
                }
                if ($store->getId() == $data['storeId']) {
                    $currentWeb = $website->getId();
                }
            }
        }
    }
    if ($web != $currentWeb) {
        $respon['success'] = false;
    }
    if (!Mage::getStoreConfig('xpos/checkout/enable_guest_checkout', $data['storeId'])) {
        $respon['success'] = true;
    }
    $this->getResponse()->setBody(json_encode($respon));
}

private
function _changeProcessingStatus($order)
{
    $status = $order->getStatus();
    $state = $order->getState();
    if ($state == self::PENDING_STATUS) {
        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
    }
    if ($status == self::PENDING_STATUS) {
        $order->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING, true);
    }
    $order->save();
}

    private function _clearAll()
    {
        /*DO: remove session customer when reload order*/
        Mage::getSingleton('adminhtml/session')->setCustomerIdReload(null);
        Mage::getSingleton('adminhtml/session')->setSelectedShipmentMethod(null);
        Mage::getSingleton('adminhtml/session')->setGiftRegistry(null);
        /* TODO: Clear GiftCard*/
        if (Mage::getModel('xpos/integrate')->isIntegrateWithGiftVoucher()) {
            Mage::getSingleton('giftvoucher/session')->clear();
            Mage::getModel('xpos/integrate')->clearGiftcardSession(Mage::getSingleton('checkout/session'));
        }

        if (Mage::getModel('xpos/integrate')->isIntegrateWithWebtexGiftCard()) {
            Mage::getSingleton('giftcards/session')->clear();
        }

        Mage::getSingleton('adminhtml/session')->setOrderViewDetail(NULL);
}

public
function testAction()
{
//        Zend_Debug::dump(Mage::getModel('sales/order_creditmemo')->load(8)->getOrder()->getData());
    Zend_Debug::dump(Mage::getModel('sales/order')->load(564)->getData());
}

}
