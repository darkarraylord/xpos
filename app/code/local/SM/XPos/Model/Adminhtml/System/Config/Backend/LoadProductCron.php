<?php

/**
 * Created by PhpStorm.
 * User: vjcspy
 * Date: 7/13/15
 * Time: 4:17 PM
 */
class SM_XPos_Model_Adminhtml_System_Config_Backend_LoadProductCron extends Mage_Core_Model_Config_Data
{
    const CRON_STRING_PATH = 'crontab/jobs/loadProductToCache/schedule/cron_expr';
    const CRON_MODEL_STRING_PATH = 'crontab/jobs/loadProductToCache/run/model';

    protected function _afterSave()
    {
        $time = $this->getData('groups/cronJob/fields/time/value');
        $frequency = $this->getData('groups/cronJob/fields/frequency');
        $frequencyDaily = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_DAILY;
        $frequencyWeekly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_WEEKLY;
        $frequencyMonthly = Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency::CRON_MONTHLY;

        $cronDayOfWeek = date('N');
        $cronDayOfMonth = date('j');

        $cronExprArray = array(
            intval($time[1]),                                   # Minute
            intval($time[0]),                                   # Hour
            ($frequency['value'] == $frequencyMonthly) ? $cronDayOfMonth : '*',       # Day of the Month
            '*',                                                # Month of the Year
            ($frequency['value'] == $frequencyWeekly) ? $cronDayOfWeek : '*',        # Day of the Week
        );
        $cronExprString = join(' ', $cronExprArray);

        try {
            Mage::getModel('core/config_data')
                ->load(self::CRON_STRING_PATH, 'path')
                ->setValue($cronExprString)
                ->setPath(self::CRON_STRING_PATH)
                ->save();
            //$value = Mage::getModel('core/config_data')->load(self::CRON_STRING_PATH, 'path');
            $model = Mage::getModel('core/config_data')->load(self::CRON_MODEL_STRING_PATH, 'path')->getData();
            if(!!!$model){
                $modelLoadProduct = 'xpos/observer::loadProductToCache';
                Mage::getModel('core/config_data')
                    ->load(self::CRON_MODEL_STRING_PATH, 'path')
                    ->setValue($modelLoadProduct)
                    ->setPath(self::CRON_MODEL_STRING_PATH)
                    ->save();
            }
            //$lastSave = new SM_XPos_Model_Adminhtml_System_Config_Backend_LastSaveSetting();
            //$lastSave->_afterSave();
        } catch (Exception $e) {
            throw new Exception(Mage::helper('cron')->__('Unable to save the cron expression.'));

        }
    }

    protected function checkAfterSave()
    {
        if (isset($this->getFieldConfig()->job)) {
            $job = $this->getFieldConfig()->job;

            $cronStringPath = 'crontab/jobs/' . $job . '/schedule/cron_expr';
            $cronModelPath = 'crontab/jobs/' . $job . '/run/model';

            $value = $this->getValue();
            $node = (string)Mage::getConfig()->getNode($cronModelPath);

            $schedule = Mage::getModel('core / config_data')
                ->load($cronStringPath, 'path');

            $model = Mage::getModel('core / config_data')
                ->load($cronModelPath, 'path');

            if ($value) {
                $schedule->setValue($value)->setPath($cronStringPath)->save();
                $model->setValue($node)->setPath($cronModelPath)->save();
            } else {
                $schedule->delete();
                $model->delete();
                $this->delete();
            }
        }
    }
}
