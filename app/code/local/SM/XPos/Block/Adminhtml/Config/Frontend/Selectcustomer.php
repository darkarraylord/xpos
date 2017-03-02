<?php
/**
 * Created by PhpStorm.
 * User: vjcspy
 * Date: 6/23/15
 * Time: 3:29 PM
 */
class SM_XPos_Block_Adminhtml_Config_Frontend_Selectcustomer extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Set template to itself
     */
    protected function _prepareLayout() {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('sm/xpos/system/config/selectCustomer.phtml');
        }

        return $this;
    }

    /**
     * Get the button and scripts contents
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $originalData = $element->getOriginalData();

        $label = $originalData['button_label'];

        $this->addData(array(
            'button_label' => $this->helper('xpos')->__($label),
            'button_url'   => 'ds',
            'html_id'      => $element->getHtmlId(),
        ));
        return $this->_toHtml();
    }

}
