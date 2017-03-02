<?php

class SM_XPos_Block_Adminhtml_Till_Render_StoreForTill extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $data = $row->getStoreId();
        $store = Mage::getModel('core/store')->load($data);
        if (!is_null($store->getData('name'))) {
            return $store->getData('name');
        } else {
            return 'Please select store in current till and save again!';
        }
    }

}
