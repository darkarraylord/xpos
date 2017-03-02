<?php
/**
 * Author: HieuNT
 * Email: hieunt@smartosc.com
 */

class SM_XPos_Block_Adminhtml_Catalog_Report_Render_Shipping extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        $method = $row->getData('shipping_method');
        $method = explode('_',$method);
        $shippingTitle = Mage::getStoreConfig('carriers/'.$method[0].'/title');
        if(!$shippingTitle && $this->isXPaymentPickupShipping($row)){
            $shippingTitle = $this->getXPaymentPickupShippingTitle();
        }
        return $shippingTitle;
    }

    /**
     * @param Varien_Object $row
     * @return bool|int
     */
    protected function isXPaymentPickupShipping(Varien_Object $row)
    {
        return strpos(
            $row->getData('shipping_method'),
            Mage::getSingleton('xpayment/pickupShippingMethod')->getCarrierCode()) !== false;
    }

    /**
     * @return mixed
     */
    protected function getXPaymentPickupShippingTitle()
    {
        return Mage::getStoreConfig('carriers/'.Mage::getSingleton('xpayment/pickupShippingMethod')->getCarrierCode().'/title');
    }
}
