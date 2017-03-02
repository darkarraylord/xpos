<?php

/**
 * Created by PhpStorm.
 * User: vjcspy
 * Date: 7/13/15
 * Time: 2:33 PM
 */
class SM_XPos_Model_Cronjob_LoadProductToCache_LoadProduct extends Mage_Core_Model_Abstract
{


    public $startTime = null;

    /*
     * Chuc nang 0.1:
     *
     * load theo store ma khach hang cai dat
     * Load den bao nhieu h thi dung laij mac du load chua xong
     * thoi gian load cua tung store cu the la bao nhieu
     *
     *
     * */

    public function runLoadProduct()
    {
        Mage::helper('xpos/realTimeProduct')->resetRealTimeUpdate();
        $string = '---------------------->Cronjob start at: ' . Mage::getModel('core/date')->date('Y-m-d H:i:s');
        Mage::getSingleton('xpos/iayz')->addLogCronjob($string);
        $stores = $this->getStoreLoadProduct();
        $stores = explode(',', $stores);
        foreach ($stores as $store) {
            $string = 'Cronjob load storeId: ' . $store . ' at ' . Mage::getModel('core/date')->date('Y-m-d H:i:s');
            Mage::getSingleton('xpos/iayz')->addLogCronjob($string);
            $page = 1;
            do {
                $checkTimeEx = $this->checkStopImmediate();
                if ($checkTimeEx['result'] == 'true') {
                    return;
                }
                $result = $this->cronLoadProduct($store, $page);
                $page += 1;
            } while ($result);
        }
        $string = 'Cronjob end at: ' . Mage::getModel('core/date')->date('Y-m-d H:i:s') . '<-----------------------------------';
        Mage::getSingleton('xpos/iayz')->addLogCronjob($string);
    }


    /*-----------------------------------------*/
    private function getStoreLoadProduct()
    {
        return $stores = Mage::getStoreConfig('xpos/cronJob/store_run');
    }

    private function checkStopImmediate()
    {
        if (!$this->startTime) {
            $this->startTime = microtime(true);

            return array(
                'long'   => 0,
                'result' => 'false',
            );
        } else {
            $currentTime = microtime(true);
            $long = floatval($currentTime - $this->startTime);
            $max = floatval(Mage::getStoreConfig('xpos/cronJob/time_stop')) * 60 * 1000;
            if ($long > $max) {
                return array(
                    'long'   => ($long / 60000),
                    'result' => 'true',
                );
            } else {
                return array(
                    'long'   => ($long / 60000),
                    'result' => 'false',
                );
            }
        }
    }

    public static function cronLoadProduct($_storeId, $_page = '1', $_warehouseId = '')
    {
        /*init Store/warehouse/page*/
        $page = $_page;
        $warehouseId = $_warehouseId;
        $storeView = $_storeId;

        /*--------------------------------------------------------------------------------*/
        $cacheKey = 'xpos_' . $page . '_' . $warehouseId . '_' . $storeView;
//        $controller = Mage::getControllerInstance('SM_XPos_Adminhtml_XposproductController',null,null);
        $result = Mage::helper('xpos/product')->getProductList(null, $page, $warehouseId);
        $cacheProducts = Mage::helper('core')->jsonEncode($result);
        if ($result['totalLoad'] == 0) {
            Mage::app()->getCache()->save(md5(microtime()), 'XPOS_DETECT_NEW_CACHE_DATA_KEY' . $storeView, array("xpos_cache"), self::getTimeSaveCache());
            Mage::app()->getCache()->save($cacheProducts, $cacheKey, array("xpos_cache"), self::getTimeSaveCache());

            return false;
        } else {
            Mage::app()->getCache()->save($cacheProducts, $cacheKey, array("xpos_cache"), self::getTimeSaveCache());

            return true;
        }
    }

    public static function getTimeSaveCache()
    {
        $fre = Mage::helper('xpos/configXPOS')->getCrontab('frequency');
        $time = 86400;
        switch ($fre) {
            case Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_DAILY:
                $time = 86400;
                break;
            case Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY:
                $time = 2592000;
                break;
            case Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY:
                $time = 604800;
                break;
        }

        return $time;

    }
}
