<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Search Customer Model
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class SM_XPos_Model_Search_Customer extends Varien_Object
{
    protected $_isXman = false;
    protected $_totalCustomer;


    public function __construct()
    {
        $this->_totalCustomer = Mage::app()->getCache()->load('totalCustomer');
    }

    /**
     * Load search results
     *
     * @return Mage_Adminhtml_Model_Search_Customer
     */
    public function load()
    {
        $arr = array();

        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);
            return $this;
        }

        $isXman = 0;
        if (Mage::getStoreConfig('xmanager/general/enabled') == 1) {
            /*xmanager has been installed*/
            $isXman = 1;
        }
        if ($isXman == 0) {
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
                ->setPage($this->getStart(), $this->getLimit())
                ->load();
        } else {
            $per = Mage::getModel('xmanager/permission');
            if ($per->getShareOrder() == 1 || $per->getPermission() == '0') {
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
                    ->setPage($this->getStart(), $this->getLimit())
                    ->load();
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
                    $this->setResults($arr);
                    return $this;
                }
                $arrAdminId = array();
                $currentId = $per->getCurrentAdmin();
                $currentId = $currentId['id'];

                $arrAdminId[] = $currentId;
                foreach ($per->getIdReceive() as $id) {
                    $arrAdminId[] = $id;
                }
                $collection = Mage::getResourceModel('customer/customer_collection')
                    ->addNameToSelect()
                    ->addAttributeToSelect('admin_id')
                    ->addAttributeToSelect('email')
                    ->addAttributeToSelect('created_at')
                    ->addAttributeToSelect('group_id')
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
                    ->addAttributeToFilter('admin_id', array('in' => $arrAdminId))
                    ->setPage($this->getStart(), $this->getLimit())
                    ->load();
            }
        }

//        $storeId = Mage::helper('xpos/product')->getCurrentSessionStoreId();
        $storeId = Mage::helper('xpos/product')->getCurrentSessionStoreId();
        $accountShare = Mage::getStoreConfig('customer/account_share/scope');
        foreach ($collection->getItems() as $customer) {
            $customer_website = $customer->getData('website_id');
            $customer_store = $customer->getData('store_id');
            if ($customer_store == 0 && $customer_website != 0) {
                $website = Mage::app()->getWebsite($customer_website);
                $customer_store = $website->getData('default_group_id');
            }
            if ($accountShare && $customer_website != Mage::app()->getStore($storeId)->getWebsiteId()) continue;
            $customerAddressId = $customer->getDefaultBilling();
            $customerShippingAddressId = $customer->getDefaultShipping();
            $customerBillingAddressId = $customer->getDefaultBilling();
//            if ($customerAddressId){
            $address = Mage::getModel('customer/address')->load($customerAddressId);
            $shippingAddress = Mage::getModel('customer/address')->load($customerShippingAddressId);
            $billingAddress = Mage::getModel('customer/address')->load($customerBillingAddressId);
//            }

            //TODO: [PENDING] If existing billing/shipping address. Will fill to the Billing/shipping form.
            if ($address->getId()) {
                $data = array(
                    'id'          => $customer->getId(),
                    'type'        => Mage::helper('adminhtml')->__('Customer'),
                    'name'        => $customer->getName(),
                    'fname'       => $customer->getData('firstname'),
                    'lname'       => $customer->getData('lastname'),
                    'email'       => $customer->getEmail(),
                    'description' => $customer->getCompany(),
                    'telephone'   => $billingAddress->getData('telephone'),
                    'group_id'    => $customer->getData('group_id'),
                    'firstname'   => $billingAddress->getData('firstname'),
                    'lastname'    => $billingAddress->getData('lastname'),
                    'city'        => $billingAddress->getData('city'),
                    'street'      => $billingAddress->getData('street'),
                    'country_id'  => $billingAddress->getData('country_id'),
                    'region'      => $billingAddress->getData('region'),
                    'region_id'   => $billingAddress->getData['region_id'],
                    'postcode'    => $billingAddress->getData('postcode'),
                    'addresses'   => $this->getCollectionAddress($customer),
                );

                if ($billingAddress) {
                    $billingAddressArray = array(
                        'billing_firstname'  => $billingAddress->getData('firstname'),
                        'billing_lastname'   => $billingAddress->getData('lastname'),
                        'billing_city'       => $billingAddress->getData('city'),
                        'billing_street'     => $billingAddress->getData('street'),
                        'billing_country_id' => $billingAddress->getData('country_id'),
                        'billing_region'     => $billingAddress->getData('region'),
                        'billing_region_id'  => $billingAddress->getData('region_id'),
                        'billing_postcode'   => $billingAddress->getData('postcode'),
                        'billing_telephone'  => $billingAddress->getData('telephone'),
                    );
                    $data = array_merge($data, $billingAddressArray);
                }


                if ($shippingAddress) {
                    $shippingAddressArray = array(
                        'shipping_firstname'  => $shippingAddress->getData('firstname'),
                        'shipping_lastname'   => $shippingAddress->getData('lastname'),
                        'shipping_city'       => $shippingAddress->getData('city'),
                        'shipping_street'     => $shippingAddress->getData('street'),
                        'shipping_country_id' => $shippingAddress->getData('country_id'),
                        'shipping_region'     => $shippingAddress->getData('region'),
                        'shipping_region_id'  => $shippingAddress->getData('region_id'),
                        'shipping_postcode'   => $shippingAddress->getData('postcode'),
                        'shipping_telephone'  => $shippingAddress->getData('telephone'),
                    );
                    $data = array_merge($data, $shippingAddressArray);
                }


            } else {
                $data = array(
                    'id'          => $customer->getId(),
                    'type'        => Mage::helper('adminhtml')->__('Customer'),
                    'name'        => $customer->getName(),
                    'fname'       => $customer->getData('firstname'),
                    'lname'       => $customer->getData('lastname'),
                    'email'       => $customer->getEmail(),
                    'description' => $customer->getCompany(),
                    'telephone'   => $customer->getTelephone(),
                    'addresses'   => $this->getCollectionAddress($customer),
                );
            }

            $arr[] = $data;
        }

        $this->setResults($arr);

        return $this;
    }

    /**
     * huypq
     * 01/04/2015
     *
     */
    protected function _initFilter()
    {
        $this->_isXman = Mage::getStoreConfig('xmanager/general/enabled') == 1;
    }

    /**
     * huypq
     * 01/04/2015
     * Count all customers
     *
     */
    public function getCountAll()
    {
        $this->_totalCustomer = Mage::app()->getCache()->load('totalCustomer');
        $total = $this->_totalCustomer;
        if (!$total) {
            $collection = Mage::getResourceModel('customer/customer_collection');
//            $storeId = Mage::helper('xpos/product')->getCurrentSessionStoreId();
            $storeId = Mage::helper('xpos/product')->getCurrentSessionStoreId();
            $accountShare = Mage::getStoreConfig('customer/account_share/scope');
            $customerInRequest = 0;
            foreach ($collection->getItems() as $customer) {
                $customer_website = $customer->getData('website_id');
                if ($accountShare == 1 && $customer_website != Mage::app()->getStore($storeId)->getWebsiteId()) continue;
                $customerInRequest += 1;
            }
            $NumOfCustomer = array(
                'total' => $customerInRequest,
            );
        } else {
            $NumOfCustomer = array(
                'total' => $total,
            );
        }
        die(Mage::helper('core')->jsonEncode($NumOfCustomer));
    }

    public function loadAll($limit, $page)
    {
        $this->_totalCustomer = Mage::app()->getCache()->load('totalCustomer');
        $customerInRequest = (int)$this->_totalCustomer;
        if ($page == 1) {
            Mage::app()->getCache()->save('0', 'totalCustomer', array("xpos_cache"), 60 * 60 * 24 * 365 * 5);
            $customerInRequest = 0;
        }
//        $collection = Mage::getResourceModel('customer/customer_collection')
//            ->addNameToSelect()
//            ->addAttributeToSelect('*')
//            ->joinAttribute('telephone', 'customer_address/telephone', 'default_billing', null, 'left');
        $isXman = 0;
        if (Mage::getStoreConfig('xmanager/general/enabled') == 1) {
            /*xmanager has been installed*/
            $isXman = 1;
        }
        $arr = array();
        if ($isXman == 1) {
            $per = Mage::getModel('xmanager/permission');
            if ($per->getShareCustomer() == 1 || $per->getPermission() == '0') {
                $collection = Mage::getResourceModel('customer/customer_collection')
                    ->addNameToSelect()
                    ->addAttributeToSelect('*')
                    ->joinAttribute('telephone', 'customer_address/telephone', 'default_billing', null, 'left');
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
                    return $this;
                }

                $arrAdminId = array();
                $currentId = $per->getCurrentAdmin();
                $currentId = $currentId['id'];

                $arrAdminId[] = $currentId;
                foreach ($per->getIdReceive() as $id) {
                    $arrAdminId[] = $id;
                }
                $collection = Mage::getResourceModel('customer/customer_collection')
                    ->addNameToSelect()
                    ->addAttributeToSelect('admin_id')
                    ->addAttributeToSelect('*')
                    ->joinAttribute('telephone', 'customer_address/telephone', 'default_billing', null, 'left')
                    ->addAttributeToFilter('admin_id', array('in' => $arrAdminId));
            }

        } else {
            $collection = Mage::getResourceModel('customer/customer_collection')
                ->addNameToSelect()
                ->addAttributeToSelect('*')
                ->joinAttribute('telephone', 'customer_address/telephone', 'default_billing', null, 'left');
        }


        if ($limit != 'all')
            $collection->setCurPage($page)->setPageSize($limit);
        if ($collection->getLastPageNumber() < $page) {
            $this->setResults(array());
            return $this;
        }
        $collection->load();

//        $storeId = Mage::getStoreConfig('xpos/general/storeid');
        $storeId = Mage::helper('xpos/product')->getCurrentSessionStoreId();
        $accountShare = Mage::getStoreConfig('customer/account_share/scope');
        foreach ($collection->getItems() as $customer) {
            $customer_website = $customer->getData('website_id');
            if ($accountShare == 1 && $customer_website != Mage::app()->getStore($storeId)->getWebsiteId()) continue;
            $customerInRequest += 1;
            $customerAddressId = $customer->getDefaultBilling();
            $customerShippingAddressId = $customer->getDefaultShipping();
            $customerBillingAddressId = $customer->getDefaultBilling();
//            if ($customerAddressId){
            $address = Mage::getModel('customer/address')->load($customerAddressId);
            $shippingAddress = Mage::getModel('customer/address')->load($customerShippingAddressId);
            $billingAddress = Mage::getModel('customer/address')->load($customerBillingAddressId);

            if ($address->getId()) {
                $data = array(
                    'id'          => $customer->getId(),
                    'type'        => Mage::helper('adminhtml')->__('Customer'),
                    'name'        => $customer->getName(),
                    'email'       => $customer->getEmail(),
                    'description' => $customer->getCompany(),
                    'telephone'   => $billingAddress->getData('telephone'),
                    'group_id'    => $customer->getData('group_id'),
                    'firstname'   => $billingAddress->getData('firstname'),
                    'lastname'    => $billingAddress->getData('lastname'),
                    'city'        => $billingAddress->getData('city'),
                    'street'      => $billingAddress->getData('street'),
                    'country_id'  => $billingAddress->getData('country_id'),
                    'region'      => $billingAddress->getData('region'),
                    'region_id'   => $billingAddress->getData('region_id'),
                    'postcode'    => $billingAddress->getData('postcode'),
                    'addresses'   => $this->getCollectionAddress($customer),
                );

                if ($billingAddress) {
                    $billingAddressArray = array(
                        'billing_firstname'  => $billingAddress->getData('firstname'),
                        'billing_lastname'   => $billingAddress->getData('lastname'),
                        'billing_city'       => $billingAddress->getData('city'),
                        'billing_street'     => $billingAddress->getData('street'),
                        'billing_country_id' => $billingAddress->getData('country_id'),
                        'billing_region'     => $billingAddress->getData('region'),
                        'billing_region_id'  => $billingAddress->getData('region_id'),
                        'billing_postcode'   => $billingAddress->getData('postcode'),
                        'billing_telephone'  => $billingAddress->getData('telephone'),
                    );
                    $data = array_merge($data, $billingAddressArray);
                }


                if ($shippingAddress) {
                    $shippingAddressArray = array(
                        'shipping_firstname'  => $shippingAddress->getData('firstname'),
                        'shipping_lastname'   => $shippingAddress->getData('lastname'),
                        'shipping_city'       => $shippingAddress->getData('city'),
                        'shipping_street'     => $shippingAddress->getData('street'),
                        'shipping_country_id' => $shippingAddress->getData('country_id'),
                        'shipping_region'     => $shippingAddress->getData('region'),
                        'shipping_region_id'  => $shippingAddress->getData('region_id'),
                        'shipping_postcode'   => $shippingAddress->getData('postcode'),
                        'shipping_telephone'  => $shippingAddress->getData('telephone'),
                    );
                    $data = array_merge($data, $shippingAddressArray);
                }


            } else {
                $data = array(
                    'id'          => $customer->getId(),
                    'type'        => Mage::helper('adminhtml')->__('Customer'),
                    'name'        => $customer->getName(),
                    'email'       => $customer->getEmail(),
                    'description' => $customer->getCompany(),
                    'telephone'   => $customer->getTelephone(),
                    'addresses'   => $this->getCollectionAddress($customer),
                );
            }


            $arr[] = $data;
        }
        Mage::app()->getCache()->save((string)$customerInRequest, 'totalCustomer', array("xpos_cache"), 60 * 60 * 24 * 365 * 5);
        $this->setResults($arr);

        return $this;
    }

    public function getCollectionAddress($customer)
    {
        $addCusCollection = array();
        $addressesCus = $customer->getAddresses();
        foreach ($addressesCus as $add) {
            $addCusCollection[] = $add->getData();
        }
        return $addCusCollection;
    }

}
