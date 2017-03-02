<?php

/**
 * Created by PhpStorm.
 * User: SMART
 * Date: 11/24/2015
 * Time: 9:03 AM
 */
class SM_XPos_Block_Adminhtml_Page_Head extends Mage_Adminhtml_Block_Page_Head
{
    const MAGEWORLD_RP_MODULE = 'MW_RewardPoints';
    const MAGESTORE_RP_MODULE = 'Magestore_RewardPoints';
    const WEBTEX_CORE_MODULE = 'Webtex_Core';


    public function isExtensionEnabled($name)
    {
        $modules = Mage::getConfig()->getNode('modules')->children();
        $modulesArray = (array)$modules;
        return (! empty($modulesArray[$name]) && $modulesArray[$name]->active == 'true');
    }

    public function getCssJsHtml(){

        if ($this->isExtensionEnabled(self::MAGEWORLD_RP_MODULE)) {
            $this->addJs('mw_rewardpoints/validate.js');
            $this->addJs('mw_rewardpoints/accordion.js');
        }

        if ($this->isExtensionEnabled(self::MAGESTORE_RP_MODULE)) {
            $this->addCss('css/magestore/rewardpoints.css');
            $this->addJs('magestore/rewardpoints.js');
            $this->addItem('skin_js', 'js/magestore/rewardpoints.js');
        }

        if ($this->isExtensionEnabled(self::WEBTEX_CORE_MODULE) && Mage::getModel('xpos/integrate')->isIntegrateWithWebtexGiftCard()) {
            $this->addJs('sm/xpos/integrate/giftRegistryWebtex.js');
        }

        if (Mage::getModel('xpos/integrate')->isIntegrateWithGiftVoucher()) {
            $this->addCss('css/magestore/giftvoucher/giftvoucher.css');
            $this->addCss('css/magestore/giftvoucher/gift_template.css');

            $this->addJs('magestore/giftvoucher/js/giftvoucher.js');
            $this->addJs('magestore/adminhtml/giftvoucher.js');
            $this->addJs('prototype/window.js');
        }

        return  parent::getCssJsHtml();
    }
    /*
                    <!--TODO: integrate Magestore RP-->
                    <action method="addCss">
                        <stylesheet>css/magestore/rewardpoints.css</stylesheet>
                    </action>
                    <action method="addJs">
                        <script>magestore/rewardpoints.js</script>
                    </action>
                    <!-- Custom JS for Backend -->
                    <action method="addItem">
                        <type>skin_js</type>
                        <name>js/magestore/rewardpoints.js</name>
                        <params/>
                    </action>

                    <!--TODO: integrate Webtex GiftRegistry-->
                    <action method="addJs">
                        <script>sm/xpos/integrate/giftRegistryWebtex.js</script>
                    </action>
                    <!--TODO: integrate MageWorld RP-->
                    <action method="addJs">
                        <script>mw_rewardpoints/validate.js</script>
                    </action>
                    <action method="addJs">
                        <script>mw_rewardpoints/accordion.js</script>
                    </action>
     */
}
