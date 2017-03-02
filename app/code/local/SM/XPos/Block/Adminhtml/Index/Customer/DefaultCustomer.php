<?php
/**
 * Created by PhpStorm.
 * User: vjcpsy
 * Date: 3/17/2015
 * Time: 18:20
 */
class SM_Xpos_Block_Adminhtml_Index_Customer_DefaultCustomer extends Mage_Core_Block_Template{
    public function checkDefaultCustomer(){
        $customerId = Mage::helper('xpos/configXPOS')->getDefaultCustomerId();

        $customer = Mage::getModel('customer/customer')->load($customerId);
        if($customer->getEmail() == null){
            return false;
        }

        if($customerId == "" || !preg_match('/([0-9]+)/i',$customerId)){
            return false;
        }

        return true;

    }
}
