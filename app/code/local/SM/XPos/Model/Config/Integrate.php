<?php

/**
 * SmartOSC Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 * @category   SM
 * @package    SM_XPOS
 * @version    2.1.12
 * @author     truongnq@smartosc.com
 * @copyright  Copyright (c) 2010-2013 SmartOSC Co. (http://www.smartosc.com)
 */
class SM_XPos_Model_Config_Integrate {

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        $array = array(
            array('value' => 0, 'label' => 'Disable')
        );
        if (Mage::getStoreConfig('xwarehouse/general/enabled') == 1)
            $array[] = array('value' => 1, 'label' => 'X-Multi-Warehouse');
        if (!!Mage::getStoreConfig('inventoryplus/general/select_warehouse'))
            $array[] = array('value' => 2, 'label' => 'Inventory Plus');

        return $array;
    }

}
