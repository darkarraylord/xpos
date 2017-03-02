<?php

    class SM_XPos_Block_Adminhtml_Override_Coupons extends Mage_Adminhtml_Block_Sales_Order_Create_Abstract {
        public function __construct() {
            parent::__construct();
            $this->setId('sales_order_create_coupons_form');
        }

        public function getCouponCode() {

            return $this->_getSession()->getQuote()->getCouponCode();
        }

        /*TODO: integrate Mage store RP*/

        public function getBlockHelper() {
            return Mage::helper('rewardpoints/block_spend');
        }

        public function getPointHelper() {
            return Mage::helper('rewardpoints/point');
        }

        public function __call($method, $args) {
            if (!Mage::getModel('xpos/integrate')->isIntegrateRp())
                return parent::__call($method, $args);

            $helper = $this->getBlockHelper();
            if (method_exists($helper, $method)) {
                return call_user_func_array(array($helper, $method), $args);
            }

            return parent::__call($method, $args);
        }

        public function getPointMoney() {
            $pointAmount = Mage::helper('rewardpoints/customer')->getBalance();
            if ($pointAmount > 0) {
                $rate = Mage::getModel('rewardpoints/rate')->getRate(Magestore_RewardPoints_Model_Rate::POINT_TO_MONEY);
                if ($rate && $rate->getId()) {
                    $baseAmount = $pointAmount * $rate->getMoney() / $rate->getPoints();

                    return Mage::app()->getStore()->convertPrice($baseAmount, true);
                }
            }

            return '';
        }
        /*end integrate RP*/




        /*Todo: Integrate with MageWorld RewardPoints*/

        protected function _getOrderCreateModel()
        {
//            return Mage::getSingleton('adminhtml/sales_order_create');
            return Mage::getSingleton('xpos/sales_create');
        }

        public function getStoreId()
        {
            $quote = $this->_getOrderCreateModel()->getQuote();;
            return $quote->getStore()->getId();
        }
        public function getRewardPointsRule()
        {
            $quote = $this->_getOrderCreateModel()->getQuote();
            $store_id = $quote->getStore()->getId();
            return Mage::helper('rewardpoints')->getCheckoutRewardPointsRule($quote,$store_id);
        }
        public function getCurrentRewardPoints()
        {
            $quote = $this->_getOrderCreateModel()->getQuote();
            $customer_id = $quote->getCustomerId();
            $store_id = $quote->getStore()->getId();
            $customer = Mage::getModel('rewardpoints/customer')->load($customer_id);
            $point = (int)$customer->getMwRewardPoint();
            $point_show = Mage::helper('rewardpoints')->formatPoints($point,$store_id);

            return $point_show;

        }
        public function getRate($store_id)
        {
            $config = Mage::helper('rewardpoints')->getPointMoneyRateConfig($store_id);
            $rate = explode("/",$config);
            return $rate;
        }
        public function formatMoney($money)
        {
            return Mage::helper('core')->currency($money);
        }
        public function getEarnPointShow()
        {
            $quote = $this->_getOrderCreateModel()->getQuote();
            $store_id = $quote->getStore()->getId();
            $earn_rewardpoint = (int)$quote->getEarnRewardpoint();
            $earn_rewardpoint_show = Mage::helper('rewardpoints')->formatPoints($earn_rewardpoint,$store_id);

            return $earn_rewardpoint_show;
        }
        public function getRewardPoints()
        {
            $quote = $this->_getOrderCreateModel()->getQuote();
            $points = $quote->getMwRewardpoint();
            return $points;
        }
        public function getMaxPointToCheckOut()
        {
            $quote = $this->_getOrderCreateModel()->getQuote();
            $quote ->collectTotals()->save();
            $store_id = $quote->getStore()->getId();
            $customer_id = $quote->getCustomerId();
            $baseGrandTotal = $quote->getBaseGrandTotal();
            $spend_point = Mage::helper('rewardpoints')->getMaxPointToCheckOut($quote,$customer_id,$store_id,$baseGrandTotal);
            $spend_point_show = Mage::helper('rewardpoints')->formatPoints($spend_point,$store_id);

            return $spend_point_show;
        }

        /*TODO: integrate with Rack RewardPoint*/
        /**
         * Get base grand total of current quote
         *
         * @return float
         */
        public function getGrandTotal()
        {
            $quote = $this->getQuote();

            return $quote->getBaseGrandTotal();
        }

        public function getRawBaseGrandTotal()
        {
            $quote = $this->getQuote();
            return (float)$quote->getBaseGrandTotal() + (float)$quote->getPointCurrencyUsed() - (float)$quote->getBaseCodFee();
        }

        /**
         * Get current used point
         *
         * @return int
         */
        public function getUsedPoint()
        {
            $session = $this->_getSession();
            if ($session->getPointUsed()) {
                return $session->getPointUsed();
            } else {
                return 0;
            }
        }

        /**
         * Get current point currency.
         *
         * @return float
         */
        public function getUsedPointCurrency()
        {
            $usedPoint = $this->getUsedPoint();
            $money = Mage::helper('rackpoint')->point2Currency($usedPoint);

            return $money;
        }

        /**
         * Get current balance
         *
         * @return Varien_Object
         */
        public function getCurrentBalance()
        {
            /* @var $balance Rack_Point_Model_Point_Balance */
            $balance = Mage::getSingleton('rackpoint/point_balance');
            $result = $balance->getBalanceOfCurrentCustomer(true, $this->_getCustomerId(), $this->_getWebsiteId());

            return $result;
        }

        /**
         * Get current quote
         *
         * @return Mage_Sales_Model_Quote
         */
        public function getQuote()
        {
            return $this->_getQuote();
        }

        public function formatCurrency($currency)
        {
            return Mage::app()->getStore()->formatPrice($currency);
        }

        public function getMinRequiredPoints()
        {
            $quote = $this->_getQuote();
            $_helper = Mage::helper('rackpoint');

            $total = $_helper->getRealBaseTotal($quote) + $quote->getPointCurrencyUsed();

            $disallowFees = $_helper->getDisallowFees();
            $disallowFees[] = 'cod_fee';
            $address = $quote->getShippingAddress();
            foreach ($disallowFees as $fee) {
                if (strpos($fee, 'shipping') !== false) {
                    $total -= $address->getData($fee);
                } elseif($fee !== '') {
                    $total -= $quote->getData($fee);
                }
            }

            $requirePoint = $_helper->currency2Point($total);
            if ($requirePoint == 0) {
                $requirePoint = $this->getUsedPoint();
            }

            return $requirePoint;
        }

        /**
         * Retrieve session object
         *
         * @return Mage_Adminhtml_Model_Session_Quote
         */
        protected function _getSession()
        {
            return Mage::getSingleton('adminhtml/session_quote');
        }

        /**
         * Retrieve quote object
         *
         * @return Mage_Sales_Model_Quote
         */
        protected function _getQuote()
        {
            return $this->_getSession()->getQuote();
        }

        /**
         * Return current website id
         * @return int|null|string
         */
        protected function _getWebsiteId() {
            return $this->_getQuote()->getStore()->getWebsiteId();
        }

        /**
         * Return current selected customer id.
         * @return int
         */
        protected function _getCustomerId() {
            return $this->_getQuote()->getCustomerId();
        }


        /*TODO: Integrate with Gift Voucher (Gift Card Magento)*/
        public function getGiftVoucherDiscount()
        {
            $session = Mage::getSingleton('checkout/session');
            $discounts = array();
            if ($codes = $session->getGiftCodes()) {
                $codesArray = explode(',', $codes);
                $codesDiscountArray = explode(',', $session->getCodesDiscount());
                $discounts = array_combine($codesArray, $codesDiscountArray);
            }
            return $discounts;
        }
    }

