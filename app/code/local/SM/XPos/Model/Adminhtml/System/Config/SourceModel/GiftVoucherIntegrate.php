<?php

/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 9/18/15
 * Time: 5:02 PM
 */
class SM_XPos_Model_Adminhtml_System_Config_SourceModel_GiftVoucherIntegrate {
    protected static $_options;
    public static $DISABLE = 'disable';
    public static $MAGESTORE_GIFTVOUCHER = 'GiftVoucher';

    public function toOptionArray() {
        self::$_options = array(
            array(
                'label' => 'Disable',
                'value' => self::$DISABLE,
            ),
        );

//            Check magestore giftcard card
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array)$modules;

        if (isset($modulesArray['Magestore_Giftvoucher'])) {
            self::$_options[] = array(
                'label' => 'Magestore Giftvoucher',
                'value' => self::$MAGESTORE_GIFTVOUCHER,
            );
        }
        return self::$_options;
    }
}
