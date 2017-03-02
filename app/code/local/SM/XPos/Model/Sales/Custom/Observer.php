<?php

/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 12/18/15
 * Time: 10:32 AM
 */
class SM_XPos_Model_Sales_Custom_Observer extends Varien_Object {

    protected $_arrayOptions = array();
    protected $_currentOption = 0;


    public function catalogProductLoadAfter(Varien_Event_Observer $observer) {
        if (!SM_XPos_Helper_CustomSalesHelper::getFlagCollect())
            return $this;
        $action = Mage::app()->getFrontController()->getAction();
        if (in_array($action->getFullActionName(), array('adminhtml_xpos_index', 'adminhtml_xpos_loadBlock', 'adminhtml_xpos_complete1', 'adminhtml_xpos_completeOffline'))) {

            $product = $observer->getProduct();
            $productId = $product->getId();

            if ($productId != Mage::helper('xpos/customSalesHelper')->getCustomSalesId())
                return $this;
            $items = $action->getRequest()->getParam('item');

            if (!is_array($items)) {
                return $this;
            }
            foreach ($items as $id => $dataOption) {
                if (strpos($id, '-') !== FALSE && strpos($id, Mage::helper('xpos/customSalesHelper')->getCustomSalesId())!== FALSE) {
                    $this->_arrayOptions[] = array(
                        'custom_sales_name' => $dataOption['name'],
                        //                        'custom_sales_price' => $dataOption['price'],
                    );
                }
            }
            $options = $this->_arrayOptions[SM_XPos_Helper_CustomSalesHelper::$COUNT_CURRENT_CUSTOM_SALES];


//            $product->setFinalPrice(500);
            // add to the additional options array
            $additionalOptions = array();
            if ($additionalOption = $product->getCustomOption('additional_options')) {
                $additionalOptions = (array)unserialize($additionalOption->getValue());
            }
            foreach ($options as $key => $value) {
                $additionalOptions[] = array(
                    'label' => $key,
                    'value' => $value,
                );
            }

            $observer->getProduct()
                ->addCustomOption('additional_options', serialize($additionalOptions));

            SM_XPos_Helper_CustomSalesHelper::setFlagCollect(false);
            SM_XPos_Helper_CustomSalesHelper::$COUNT_CURRENT_CUSTOM_SALES++;
            return $this;
        }
        return $this;
    }

    public function salesConvertQuoteItemToOrderItem(Varien_Event_Observer $observer) {
        $quoteItem = $observer->getItem();
        if ($additionalOptions = $quoteItem->getOptionByCode('additional_options')) {
            $orderItem = $observer->getOrderItem();
            $options = $orderItem->getProductOptions();
            $options['additional_options'] = unserialize($additionalOptions->getValue());
            $orderItem->setProductOptions($options);
        }
    }
}
