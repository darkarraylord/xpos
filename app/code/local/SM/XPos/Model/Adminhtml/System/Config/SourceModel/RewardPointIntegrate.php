<?php

/**
 * Created by IntelliJ IDEA.
 * User: vjcspy
 * Date: 9/18/15
 * Time: 5:24 PM
 */
class SM_XPos_Model_Adminhtml_System_Config_SourceModel_RewardPointIntegrate {
    protected static $_options;
    public static $DISABLE = 'disable';
    public static $MageStore = 'MageStore';
    public static $MageWorld = 'Mageworld';
    public static $Rack = 'Rack';

    public function toOptionArray() {
        self::$_options = array(
            array(
                'label' => 'Disable',
                'value' => self::$DISABLE,
            ),
        );

        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array)$modules;

        if (isset($modulesArray['Magestore_RewardPoints'])) {
            self::$_options[] = array(
                'label' => 'Magestore Reward Points',
                'value' => self::$MageStore,
            );
        }

        if (Mage::getStoreConfig('rewardpoints/config/enabled')) {
            self::$_options[] = array(
                'label' => 'MageWorld Reward Points',
                'value' => self::$MageWorld,
            );
        }

        if (Mage::getStoreConfig('rackpoint/config/enable')) {
            self::$_options[] = array(
                'label' => 'Rack Reward Points',
                'value' => self::$Rack,
            );
        }
        return self::$_options;
    }
}
