<?php

/**
 * Author: Hieunt
 * Date: 4/5/13
 * Time: 9:45 AM
 */
class SM_XPos_Block_Adminhtml_Cashier_Edit_Tabs_Account extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('ocm_form', array('legend' => Mage::helper('xpos')->__('Account')));

        $fieldset->addField('username', 'text', array(
            'label' => Mage::helper('xpos')->__('Username'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'username',
        ));

        $fieldset->addField('password', 'password', array(
            'label' => Mage::helper('xpos')->__('Password'),
            'required' => true,
            'name' => 'password',
        ));

        if (Mage::helper('core')->isModuleEnabled('SM_XPosAPI')) {
            $fieldset->addField('pin_code', 'password', array(
                'label' => Mage::helper('xposapi')->__('Pin Code'),
                'class' => 'required-entry validate-digits validate-length maximum-length-6 minimum-length-4',
                'required' => true,
                'name' => 'pin_code',
                'after_element_html' => '<small>Pincode just have 4 or 6 numbers</small>'
            ));
        }

        $fieldset->addField('is_active', 'checkbox', array(
            'label' => Mage::helper('xpos')->__('Active'),
            'onclick' => 'this.value = this.checked ? 1 : 0;',
            'name' => 'is_active',
        ));

        $fieldset->addField('type', 'checkbox', array(
            'label' => Mage::helper('xpos')->__('Admin'),
            'onclick' => 'this.value = this.checked ? 1 : 0;',
            'name' => 'type',
        ));

        $data_admin = Mage::getModel('admin/user')->getCollection();
        $data_admin->addFieldToFilter('is_active', array('eq' => 1));

        $listAdmin = array();
        foreach ($data_admin as $row) {
            $listAdmin[] = array('value' => $row->getUserId(), 'label' => $row->getUsername());
        }

        $fieldset->addField('user_id', 'select', array(
            'label' => Mage::helper('xpos')->__('User Cashier'),
            'values' => $listAdmin,
            'class' => 'required-entry',
            'required' => true,
            'name' => 'user_id',
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $field = $fieldset->addField('store_id', 'multiselect', array(
                'name' => 'store_id[]',
                'label' => Mage::helper('cms')->__('Store View'),
                'title' => Mage::helper('cms')->__('Store View'),
                'required' => true,
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
            $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField('store_id', 'hidden', array(
                'name' => 'store_id',
                'value' => Mage::app()->getStore(true)->getId()
            ));
        }

//        $fieldset->addField('auto_login', 'checkbox', array(
//            'label' => Mage::helper('xpos')->__('Auto login In X-Pos'),
//            'onclick' => 'this.value = this.checked ? 1 : 0;',
//            'name' => 'auto_login',
//        ));

        if (Mage::getSingleton('adminhtml/session')->getCashierData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getCashierData());
            Mage::getSingleton('adminhtml/session')->getCashierData(null);
        } elseif (Mage::registry('cashier_data')) {
            $form->setValues(Mage::registry('cashier_data')->getData());
            $formData = Mage::registry('cashier_data')->getData();
            $formData['store_id'] = isset($formData['store_id'])?json_decode($formData['store_id'], true):0;
            $form->getElement('is_active')->setIsChecked(!empty($formData['is_active']));
            $form->getElement('type')->setIsChecked(!empty($formData['type']));
//            $form->getElement('auto_login')->setIsChecked(!empty($formData['auto_login']));
            $form->getElement('store_id')->setValue($formData['store_id']);

        }

    }
}
