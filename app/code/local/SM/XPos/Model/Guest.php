<?php

/**
 * Created by PhpStorm.
 * User: xxx
 * Date: 3/17/2015
 * Time: 11:23
 */
class SM_XPos_Model_Guest extends Mage_Core_Model_Abstract {
    private $_customerShippingAdd;
    private $_customerBillingAdd;
    private $_sourceCustomer;
    private $_startPage = 1; /* Start page use in collection*/
    private $_limitRecord = 10; /*Limit data use in collection*/
    protected $_orderBy = 'id';
    protected $_dir = 'desc';
    protected $_web = 'base';
    protected $_store = 'default';


    protected $_tableColumns = array(
        'entity_id',
        'name',
        'email',
        'billing_telephone',
        'billing_country_id',
        'billing_region',
        'website_id',
    );

    public function _construct() {
        parent::_construct();
        $this->_init('xpos/guest');
    }

    public function getDefaultCustomer() {
        /*DO: get session when reload order*/
        $customerId = Mage::getSingleton('adminhtml/session')->getCustomerIdReload();
        if (!is_null($customerId)) {
            $defaultCustomerId = $customerId;
            Mage::getSingleton('adminhtml/session')->setCustomerIdReload(null);
//            $customer = Mage::getModel('customer/customer')->load($customerId)->getData();
        } else
            $defaultCustomerId = Mage::helper('xpos/configXPOS')->getDefaultCustomerId(Mage::helper('xpos/product')->getCurrentSessionStoreId());
        $this->_sourceCustomer = $this->_getCustomerCreateModel()->load($defaultCustomerId);
        $this->getDefaultBillingAdd($this->_sourceCustomer);
        $this->getDefaultShippingAdd($this->_sourceCustomer);
        $customerData = array(
            'account' => array(
                'id' => $defaultCustomerId,
                'type' => 'Customer',
                'group_id' => $this->_groupId,
                'email' => $this->_sourceCustomer->getEmail(),
            ),
            'billing_address' => array(
                'customer_address_id' => $this->_sourceCustomer->getCustomerAddressId(),
                'prefix' => '',
                'firstname' => $this->_sourceCustomer->getFirstname(),/*s*/
                'middlename' => '',
                'lastname' => $this->_sourceCustomer->getLastname(),/*s*/
                'suffix' => '',
                'company' => '',
                'street' => $this->getStreet($this->_customerBillingAdd->getStreet()),/*s*/
                'city' => $this->_customerBillingAdd->getCity(),/*s*/
                'country' => $this->_getCountryCreateModel()->load($this->_customerBillingAdd->getCountryId())->getName(),
                'country_id' => $this->_customerBillingAdd->getCountryId(),/*s*/
                'region' => $this->_customerBillingAdd->getRegion(),/*s*/
                'region_id' => $this->_customerBillingAdd->getRegionId(),/*s*/
                'postcode' => $this->_customerBillingAdd->getPostcode(),/*s*/
                'telephone' => $this->_customerBillingAdd->getTelephone(),/*s*/
                'fax' => '',
            ),
            'shipping_address' => array(
                'customer_address_id' => $this->_sourceCustomer->getCustomerAddressId(),
                'prefix' => '',
                'firstname' => $this->_sourceCustomer->getFirstname(),
                'middlename' => '',
                'lastname' => $this->_sourceCustomer->getLastname(),
                'suffix' => '',
                'company' => '',
                'street' => $this->getStreet($this->_customerShippingAdd->getStreet()),/*s*/
                'city' => $this->_customerShippingAdd->getCity(),/*s*/
                'country_id' => $this->_customerShippingAdd->getCountryId(),/*s*/
                'country' => $this->_getCountryCreateModel()->load($this->_customerShippingAdd->getCountryId())->getName(),
                'region' => $this->_customerShippingAdd->getRegion(),/*s*/
                'region_id' => $this->_customerShippingAdd->getRegionId(),/*s*/
                'postcode' => $this->_customerShippingAdd->getPostcode(),/*s*/
                'telephone' => $this->_customerShippingAdd->getTelephone(),/*s*/
                'fax' => '',
            ),
        );

        return $customerData;
    }

    public function getStreet($street) {
//        $this->helper('customer/address')->getStreetLines();
        $value = '';
        foreach ($street as $k => $v) {
            if ($k != 0 && $v != '') {
                $value .= ', ' . $v;
            } else {
                $value .= $v;
            }
        }

        return $value;
    }

    public function _getCustomerCreateModel() {
        return Mage::getModel('customer/customer');
    }

    public function _getCustomerAddCreateModel() {
        return Mage::getModel('customer/address');
    }

    public function getDefaultShippingAdd($customer) {
        $add = $customer->getDefaultShipping();
        $this->_customerShippingAdd = $this->_getCustomerAddCreateModel()->load($add);
    }

    public function getDefaultBillingAdd($customer) {
        $add = $customer->getDefaultBilling();
        $this->_customerBillingAdd = $this->_getCustomerAddCreateModel()->load($add);
    }

    public function _getCountryCreateModel() {
        return Mage::getModel('directory/country');
    }

    public function getListSearchCustomer() {
        $arr = array();

        if (!$this->hasQuery()) {
            $this->setResults($arr);

            return $this;
        }

        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('*')
            ->joinAttribute('telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('street', 'customer_address/street', 'default_billing', null, 'left')
            ->addAttributeToFilter(array(
                array('attribute' => 'firstname', 'like' => '%' . $this->getQuery() . '%'),
                array('attribute' => 'lastname', 'like' => '%' . $this->getQuery() . '%'),
                array('attribute' => 'name', 'like' => '%' . $this->getQuery() . '%'),
                array('attribute' => 'telephone', 'like' => $this->getQuery() . '%'),
                array('attribute' => 'email', 'like' => '%' . $this->getQuery() . '%'),
            ))
            ->setPage($this->_startPage, $this->_limitRecord)
            ->load();

//        $storeId = Mage::helper('xpos/product')->getCurrentSessionStoreId();
        $accountShare = Mage::getStoreConfig('customer/account_share/scope');
        foreach ($collection->getItems() as $customer) {
            $customer_website = $customer->getData('website_id');
            $customer_store = $customer->getData('store_id');
            if ($customer_store == 0 && $customer_website != 0) {
                $website = Mage::app()->getWebsite($customer_website);
                $customer_store = $website->getData('default_group_id');
            }
//            if ($accountShare && $customer_website != Mage::app()->getStore($storeId)->getWebsiteId()) continue;
            $customerAddressId = $customer->getDefaultBilling();
            $customerShippingAddressId = $customer->getDefaultShipping();
            $customerBillingAddressId = $customer->getDefaultBilling();
            $address = Mage::getModel('customer/address')->load($customerAddressId);
            $shippingAddress = Mage::getModel('customer/address')->load($customerShippingAddressId);
            $billingAddress = Mage::getModel('customer/address')->load($customerBillingAddressId);

            if ($address->getId()) {
                $data = array(
                    'id' => $customer->getId(),
                    'type' => Mage::helper('adminhtml')->__('Customer'),
                    'name' => $customer->getName(),
                    'fname' => $customer->getData('firstname'),
                    'lname' => $customer->getData('lastname'),
                    'email' => $customer->getEmail(),
                    'description' => $customer->getCompany(),
                    'telephone' => $billingAddress->getData('telephone'),
                    'group_id' => $customer->getData('group_id'),
                    'firstname' => $billingAddress->getData('firstname'),
                    'lastname' => $billingAddress->getData('lastname'),
                    'city' => $billingAddress->getData('city'),
                    'street' => $billingAddress->getData('street'),
                    'country_id' => $billingAddress->getData('country_id'),
                    'region' => $billingAddress->getData('region'),
                    'region_id' => $billingAddress->getData['region_id'],
                    'postcode' => $billingAddress->getData('postcode'),
                );

                if ($billingAddress) {
                    $billingAddressArray = array(
                        'billing_firstname' => $billingAddress->getData('firstname'),
                        'billing_lastname' => $billingAddress->getData('lastname'),
                        'billing_city' => $billingAddress->getData('city'),
                        'billing_street' => $billingAddress->getData('street'),
                        'billing_country_id' => $billingAddress->getData('country_id'),
                        'billing_region' => $billingAddress->getData('region'),
                        'billing_region_id' => $billingAddress->getData('region_id'),
                        'billing_postcode' => $billingAddress->getData('postcode'),
                        'billing_telephone' => $billingAddress->getData('telephone'),
                    );
                    $data = array_merge($data, $billingAddressArray);
                }


                if ($shippingAddress) {
                    $shippingAddressArray = array(
                        'shipping_firstname' => $shippingAddress->getData('firstname'),
                        'shipping_lastname' => $shippingAddress->getData('lastname'),
                        'shipping_city' => $shippingAddress->getData('city'),
                        'shipping_street' => $shippingAddress->getData('street'),
                        'shipping_country_id' => $shippingAddress->getData('country_id'),
                        'shipping_region' => $shippingAddress->getData('region'),
                        'shipping_region_id' => $shippingAddress->getData('region_id'),
                        'shipping_postcode' => $shippingAddress->getData('postcode'),
                        'shipping_telephone' => $shippingAddress->getData('telephone'),
                    );
                    $data = array_merge($data, $shippingAddressArray);
                }


            } else {
                $data = array(
                    'id' => $customer->getId(),
                    'type' => Mage::helper('adminhtml')->__('Customer'),
                    'name' => $customer->getName(),
                    'fname' => $customer->getData('firstname'),
                    'lname' => $customer->getData('lastname'),
                    'email' => $customer->getEmail(),
                    'description' => $customer->getCompany(),
                    'telephone' => $customer->getTelephone(),
                );
            }

            $arr[] = $data;
        }

    }

    public function  getListCustomerForSelectInSetting($requestData) {
        $this->_web = isset($requestData['website']) ? $requestData['website'] : $this->_startPage;
        $this->_store = isset($requestData['store']) ? $requestData['store'] : $this->_startPage;
        $this->_startPage = isset($requestData['start']) ? $requestData['start'] : $this->_startPage;
        $this->_limitRecord = isset($requestData['length']) ? $requestData['length'] : $this->_limitRecord;
        $this->_orderBy = isset($requestData['order'][0]['column']) ? $this->_tableColumns[$requestData['order'][0]['column']] : $this->_orderBy;
        $this->_dir = isset($requestData['order'][0]['dir']) ? $requestData['order'][0]['dir'] : $this->_dir;
        $arr = array();
        /*
         * ID
         * Name
         * Email
         * Telephone
         * Country
         * State
         * Website
         * */
        $websiteId = 0;
        foreach (Mage::app()->getWebsites(true) as $website) {
            if ($this->_web == $website->getCode()) {
                $websiteId = $website->getId();
            }
        }
        /** @var Mage_Customer_Model_Resource_Customer_Collection $collection */
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left')
            ->addAttributeToFilter('website_id',array('eq'=> $websiteId))
            ->addOrder($this->_orderBy, $this->_dir);

        if (!empty($requestData['search']['value'])) {
            $filters = array('entity_id', 'name', 'email');
            $searchString = $requestData['search']['value'];
            $filtersQuery = array();
            foreach ($filters as $filter) {
                $filtersQuery[] = array(
                    'attribute' => $filter,
                    'like' => '%' . $searchString . '%'
                );
            }
            $collection->addAttributeToFilter($filtersQuery);
        }

        $recordsFiltered = $collection->getSize();
        $collection
//            ->setPage($this->_startPage, $this->_limitRecord)
            ->setPageSize($this->_limitRecord)
            ->setCurPage(intval($this->_startPage/$this->_limitRecord)+1)
            ->load();
        foreach ($collection->getItems() as $customer) {
            $websiteName = '';
            foreach (Mage::app()->getWebsites(true) as $website) {
                if ($customer->getData('website_id') == $website->getId()) {
                    $websiteName = $website->getDataUsingMethod('name');
                }
            }
            $data = array(
                $customer->getData('entity_id'),
                $customer->getData('name'),
                $customer->getData('email'),
                $customer->getData('billing_telephone'),
                $customer->getData('billing_country_id'),
                $customer->getData('billing_region'),
                $websiteName,
            );
            $arr[] = $data;
        }
        $result = array(
            'data' => $arr,
            'recordsTotal' => Mage::getResourceModel('customer/customer_collection')->getSize(),
            'recordsFiltered' => $recordsFiltered,
        );

//        $data = array(
//            'data'=>array([1,2,3,4,5,6,7],[1,2,3,4,5,6,7])
//        );
        return $result;
    }

    /*TO set Add default customer to cache*/
    public function getAddDefaultCustomer() {
        $customerDefaultData = $this->getDefaultCustomer();
        $billingAdd = new Varien_Object();
        $billingAdd->setCountryId($customerDefaultData['billing_address']['country_id'])
            ->setRegionId($customerDefaultData['billing_address']['region_id'])
            ->setPostcode($customerDefaultData['billing_address']['postcode']);
        $shippingAdd = new Varien_Object();
        $shippingAdd->setCountryId($customerDefaultData['shipping_address']['country_id'])
            ->setRegionId($customerDefaultData['shipping_address']['region_id'])
            ->setPostcode($customerDefaultData['shipping_address']['postcode']);

        return array(
            'billingAdd' => $billingAdd,
            'shippingAdd' => $shippingAdd,
        );
    }
}
