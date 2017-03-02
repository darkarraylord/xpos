<?php
/**
 * Created by PhpStorm.
 * User: Huy
 * Date: 7/20/2015
 * Time: 11:19 AM
 *
 * @Credit: http://www.atwix.com/magento/add-button-to-system-configuration/
 */

class SM_XPos_Block_Adminhtml_XPos_Receipt_Config_PreviewButton extends Mage_Adminhtml_Block_System_Config_Form_Field {
    /*
     * Set template
     */
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('sm/xpos/receipt/config/previewbutton.phtml');
    }

    /*
     * Return element html
     *
     * @param
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        return  $this->_toHtml();
    }

    /*
     * Return AJAX URL for button
     *
     * @return string
     */
    public function getAjaxCheckUrl() {
        return Mage::helper('adminhtml')->getUrl('adminhtml/adminhtml_xpos/previewReceipt');
    }

    /*
     * Generate Button HTML
     *
     * @return string
     */
    public function getButtonHtml() {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'id'        => 'receipt_preview_btn',
                'label'     => $this->helper('adminhtml')->__('Preview'),
                'onclick'   => 'javascript:xPOSReceiptPreview(); return false;'
            ));

        return $button->toHtml();
    }
}