<?php

    /**
     * Date: 1/28/13
     * Time: 4:54 PM
     */
    class SM_XPos_Model_Observer extends Mage_Core_Model_Abstract
    {
        static $orderAfterSave   = false;
        static $invoiceSaveAfter = false;
        private $_pathConfig = 'adminhtml_system_config_edit';
        private $_pathController = 'adminhtml_xpos_index';


        public function customerSave()
        {
            $dir = Mage::getBaseDir('media') . DS . 'smartosc';
            $customer_file = $dir . DS . 'customer.json';
            $last_modify = filemtime($customer_file);
            $period = time() - $last_modify;
            $check_validate = $period > 86400 ? true : false;
            if ($last_modify == false || $check_validate) {
                $searchInstance = new SM_XPos_Model_Adminhtml_Search_Customer;
                $results = $searchInstance
                    ->loadAll('all', 1);
                if (!empty($results)) {
                    $json = '';
                    $json = json_encode($results->getResults());
                    if (!is_dir_writeable($dir)) {
                        $file = new Varien_Io_File;
                        $file->checkAndCreateFolder($dir);
                    }
                    file_put_contents($dir . DS . 'customer.json', $json);
                }
            }
        }

        public function orderSaveAfter($observer)
        {
            $isXpos = Mage::app()->getRequest()->getParam('xpos');
            $xpos_user_id = Mage::app()->getRequest()->getParam('xpos_user_id');
            $till_id = Mage::app()->getRequest()->getParam('till_id');
            if ((!empty($isXpos) || !empty($xpos_user_id)) && !self::$orderAfterSave) {
                self::$orderAfterSave = true;
                $order = $observer->getEvent()->getOrder();
                if (!empty($isXpos)) {
                    $order->setData('xpos', 1);
                }
                if (!empty($xpos_user_id)) {
                    $order->setData('xpos_user_id', $xpos_user_id);
                }
                if (!empty($till_id)) {
                    $order->setData('till_id', $till_id);
                }
                $tillData = Mage::getModel('xpos/till')->getCollection()->addFieldToFilter("till_id", $till_id)->getFirstItem();
                if (Mage::helper('xpos')->isIntegrateWithMageStoreMWH(2)){
                    $warehouse_id = Mage::getSingleton('inventoryxpos/session')->getWarehouseId();
                    if ($warehouse_id == null) $warehouse_id = $tillData->getData("warehouse_id");
                }
                $order->save();
            }
        }

        public function invoiceSaveAfter($observer)
        {

            $xpos_user_id = Mage::app()->getRequest()->getParam('xpos_user_id');
            if (!empty($xpos_user_id) && !self::$invoiceSaveAfter) {
                self::$invoiceSaveAfter = true;
                $invoice = $observer->getEvent()->getInvoice();
                if (!empty($xpos_user_id)) {
                    $invoice->setData('xpos_user_id', $xpos_user_id);
                }
                $invoice->save();
            }
        }

        public function refreshLifetime($observer)
        {
            $collection = Mage::getModel('sales/order')->getCollection();
            $collection->addFieldToFilter('xpos', array('notnull' => 1));
            $write = Mage::getSingleton("core/resource")->getConnection("core_write");
            $tableName = Mage::getSingleton('core/resource')->getTableName('sales_flat_order');
            foreach ($collection as $item) {
                $createAt = $item->getData('created_at');
                $updateAt = $item->getData('updated_at');
                $query = "UPDATE {$tableName} SET tzo_created_at = '{$createAt}', tzo_updated_at = '{$updateAt}' WHERE entity_id = " . $item->getEntityId();
                $write->query($query);
            }
        }

        public function getDefaultBillingAdd($customer)
        {
            $add = $customer->getDefaultBilling();

            return $this->_getCustomerAddCreateModel()->load($add);
        }

        public function _getCustomerAddCreateModel()
        {
            return Mage::getModel('customer/address');
        }

    public function getDataFromServer($observer)
    {
        Mage::helper('xpos/message')->getDataFromServer($observer);

        $action = $observer->getEvent()->getAction();
        $section = $action->getRequest()->getParam('section');
        $actionName = $action->getFullActionName();
        $licence = Mage::getStoreConfig('xpos/general/key');
        if(!is_null($licence)){
            if ($actionName == $this->_pathController) {
                $check = Mage::helper('xpos/license')->checkLicense("xpos", $licence);
                if(!is_array($check) && $check){
                    return true;
                }
                if (!$check['status'] || $check['status'] === 'block') {
                    Mage::app()->getResponse()->setRedirect(Mage::getUrl("adminhtml/system_config/edit/section/xpos"));
                }
                if ($check['status'] === 'warning') {
                    $observer->getEvent()->getLayout()->getUpdate()
                        ->addHandle('adminhtml_xpos_notify');
                }
                return true;
            }
            if ($actionName == $this->_pathConfig && $section == 'xpos') {
                $check = Mage::helper('xpos/license')->checkLicense("xpos",$licence);
                $session = Mage::getSingleton('adminhtml/session');
                if (!$check['status'] || $check['status'] === 'block') {
                    $observer->getEvent()->getLayout()->getUpdate()
                        ->addHandle('adminhtml_xpos_block');
                } else {
                    $session->addSuccess('The configuration has been saved!');
                    $session->addSuccess('The license key is valid!');
                }
            }
        }

    }

        public function checkSaveConfig()
        {
            $currentAdminUserName = Mage::getModel('xpos/iayz')->getCurrentAdmin();
            $currentAdminUserName = $currentAdminUserName['userName'];
            $currentTime = Mage::getModel('core/date')->date('Y-m-d H:i:s');
            $config = new Mage_Core_Model_Config();
            $result = $currentAdminUserName . ' at ' . $currentTime;
            $config->saveConfig('xpos/general/last_save_setting', $result);

            return;
            /*TODO: Set infomation to config.*/
            $defaultCustomerId = Mage::helper('xpos/configXPOS')->getDefaultCustomerId();
            $customer = Mage::getModel('customer/customer')->load($defaultCustomerId);
            $billingAdd = $this->getDefaultBillingAdd($customer);
            $guestName = $customer->getFirstname() . $customer->getLastname();
            $guestStreet = $billingAdd->getStreet();
            $street = $guestStreet[0];
            $guestCity = $billingAdd->getCity();
            $guestCountryId = $billingAdd->getCountryId();
            $guestRegionId = $billingAdd->getRegionId();
            $guestPostalCode = $billingAdd->getPostcode();
            $guestPhone = $billingAdd->getTelephone();

            Mage::getModel('core/config')->saveConfig('xpos/guest/guest_name', $guestName);
            Mage::getModel('core/config')->saveConfig('xpos/guest/guest_street', $street);
            Mage::getModel('core/config')->saveConfig('xpos/guest/guest_city', $guestCity);
            Mage::getModel('core/config')->saveConfig('xpos/guest/country_id', $guestCountryId);
            Mage::getModel('core/config')->saveConfig('xpos/guest/region_id', $guestRegionId);
            Mage::getModel('core/config')->saveConfig('xpos/guest/guest_postal_code', $guestPostalCode);
            Mage::getModel('core/config')->saveConfig('xpos/guest/guest_phone', $guestPhone);

        }

        public function refreshStatus($observer)
        {
            ob_start();
            $product = explode("_", $observer['event']['name']);
            $product = $product[count($product) - 1];

            switch ($product) {
                case "barcode":
                    $product2 = SM_Barcode_Helper_Abstract::PRODUCT;
                    break;
                case "xwarehouse":
                    $product2 = SM_XWarehouse_Helper_Abstract::PRODUCT_NAME;
                    break;
                case "xb2b":
                    $product2 = "X-B2B";
                    break;
                case "xposapi":
                    $product2 = "X-POS App";
                    break;
                default:
                    $product2 = $product;
                    break;
            }

            $dir = Mage::getBaseDir("var") . DS . "smartosc" . DS . strtolower(substr($product2, 0, 5)) . DS;
            $filepath = $dir . "license.dat";
            $file = new Varien_Io_File;
            $file->rm($filepath);
            Mage::helper('xpos/license')->checkLicense($product2, Mage::getStoreConfig($product . '/general/key'), true);
            Mage::getConfig()->cleanCache();
        }

        public function handle_adminSystemConfigChangedSection()
        {
            $storeId = Mage::getStoreConfig('xpos/general/storeid', Mage::app()->getStore());
            $customerId = Mage::helper('xpos/configXPOS')->getDefaultCustomerId(Mage::app()->getStore());
            $customerModelXPos = Mage::getModel('xpos/customer');
            $result = $customerModelXPos->checkDefaultCustomerWithXposWebsite($customerId, $storeId);
            $enableGuestCheckout = Mage::helper('xpos/configXPOS')->getEnableGuestCheckout();
            if (!$result && $enableGuestCheckout == 1) {
                //Mage::getConfig()->saveConfig('xpos/guest/enable_guest_checkout', 0);
                Mage::helper('xpos/configXPOS')->setEnableGuestCheckout(0);
                Mage::getConfig()->reinit();
                Mage::app()->reinitStores();
                Mage::throwException("Default Customer does not belong to the website where X-POS is running on");
            }
        }

        public function loadProductToCache()
        {
            $string = '---------------------->Cronjob start at: ' . Mage::getModel('core/date')->date('Y-m-d H:i:s');
            Mage::getSingleton('xpos/iayz')->addLogCronjob($string);
            Mage::getModel('xpos/cronjob_loadProductToCache_loadProduct')->runLoadProduct();
        }

        public function assignNewProduct($observer)
        {
            Mage::helper('xpos/realTimeProduct')->proNeedReload($observer['productId'], $observer['storeId']);
        }

        /*TODO: integrate with Rack Reward point*/
        public function xposProcessData($observer)
        {
            Mage::getModel('xpos/integrate')->processData($observer);
        }
    }
