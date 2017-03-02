<?php

/**
 * Created by PhpStorm.
 * User: mr.vjcspy
 * Date: 4/4/2015
 * Time: 14:52
 */
class SM_XPos_Helper_Message extends Mage_Core_Helper_Abstract
{
    public $_dataFromServer;


    /**
     * @param $observer
     */
    public function getDataFromServer($observer)
    {
        if (true || $less = ((int)$this->getTimeFromLastCheckUpdate()) >= 7) {
            $action = $observer->getEvent()->getAction();
            $actionName = $action->getFullActionName();
            if ($actionName == 'adminhtml_dashboard_index') {
                $this->sendDataToServer();
            }
        }
    }

    public function sendDataToServer()
    {
        $dataToSend = $this->dataToSend();
        if ($dataToSend['id'] == null || $dataToSend['id'] == '') $dataToSend['id'] = 0;
        $url = 'http://xpos.smartosc.com/xpos-message/index.php' . '?time_update=' . $dataToSend['id'] . '&version=' . $dataToSend['version'];
        $json = file_get_contents($url);
        $data = json_decode($json, true);
        $this->dataFromServer($data);

    }

    public function dataToSend()
    {
        date_default_timezone_set('UTC');
        $lastDateUpdate = Mage::getStoreConfig('xpos/general/last_check_update');
        $lastTimeUpdate = strtotime($lastDateUpdate);
        if ($lastTimeUpdate == null || $lastTimeUpdate == false || $lastTimeUpdate == 0) {
            $lastTimeUpdate = 0;
        }
        $data = array(
            'id'      => $lastTimeUpdate,
            'version' => floatval(substr(file_get_contents(Mage::getModuleDir(null, 'SM_XPos') . DS . 'version.txt'), 10)),
        );

        return $data;

    }

    public function dataFromServer($content)
    {
        /* ----------------------------DATA FROM SERVER---------------------------------
            data:
                'status' -> 0:no thing/1: send
                'result' :
                    'data_message':
                        'title' -> title of message
                        'content' -> content of message
                        'link' -> link of message
                        'type' -> param, 0:save last check update and not do any thing / 1: send Critical message/ 2: send Major message/ 3: send Minor message/ 4: send notice message
                    'time_server_update' -> time from save to save last check update
                'isInternal' -> true/false - default true
        */
        if(empty($content['result']['data_message'])){
            return;
        }
        $data = array(
            'status'             => $content['status'],
            'title'              => $content['result']['data_message']['title'],
            'information'        => $content['result']['data_message']['content'],
            'url'                => $content['result']['data_message']['link'],
            'isInternal'         => 'true',
            'param'              => $content['result']['data_message']['type'],
            'time_server_update' => $content['result']['time_server_update'],
        );
        $this->_dataFromServer = $data;
        $this->sendNoticeFromServer($data);
    }

    public function sendNoticeFromServer($data)
    {
        $status = $data['status'];
        $param = $data['param'];

        if ($status == 0) {
            $this->setLastCheckUpdate($data['time_server_update']);

            return;
        }
        switch ($param) {
            case 0:
                $this->setLastCheckUpdate($data['time_server_update']);
                break;
            case 1:
                $sendMessage = Mage::getSingleton('adminnotification/inbox');
                $sendMessage->addCritical($data['title'], $data['information'], $data['url'], $data['isInternal']);
                $this->setLastCheckUpdate($data['time_server_update']);
                break;
            case 2:
                $sendMessage = Mage::getSingleton('adminnotification/inbox');
                $sendMessage->addMajor($data['title'], $data['information'], $data['url'], $data['isInternal']);
                $this->setLastCheckUpdate($data['time_server_update']);
                break;
            case 3:
                $sendMessage = Mage::getSingleton('adminnotification/inbox');
                $sendMessage->addMinor($data['title'], $data['information'], $data['url'], $data['isInternal']);
                $this->setLastCheckUpdate($data['time_server_update']);
                break;
            case 4:
                $sendMessage = Mage::getSingleton('adminnotification/inbox');
                $sendMessage->addNotice($data['title'], $data['information'], $data['url'], $data['isInternal']);
                $this->setLastCheckUpdate($data['time_server_update']);
                break;
        }
    }

    function setLastCheckUpdate($timeFromServer)
    {
        date_default_timezone_set('UTC');
        $unixTime = time();
        $currentTime = date("m/d/y", $unixTime);
        $dateFromSv = date('m/d/y', $timeFromServer);
        Mage::getModel('core/config')->saveConfig('xpos/general/last_check_update', $dateFromSv);
    }

    function getTimeFromLastCheckUpdate()
    {
        date_default_timezone_set('UTC');
        $currentTime = time();
        $lastTimeUpdate = Mage::getStoreConfig('xpos/general/last_check_update');;
        $time = strtotime($lastTimeUpdate);
        if ($time == null || $time == false || $time == 0) {
            $time = 0;
        }
        $dayOfYearLastCheck = date('z', $time);
        if ((date('z', $currentTime) - $dayOfYearLastCheck) < 0 && ($currentTime > $time) && (363 - $dayOfYearLastCheck + date('z', $currentTime)) >= 7) {
            return 8;
        }

        return date('z', $currentTime) - $dayOfYearLastCheck;
    }

//    public function checkUpdate()
//    {
//        $data = $this->_dataFromServer;
//        if ($data['version'] != '') {
//            $isUpdate = false;
//            $xposCurrentVersion = floatval(substr(file_get_contents(Mage::getModuleDir(null, 'SM_XPos') . DS . 'version.txt'), 10));
//            $xposServerVersion = floatval($data['version']);
//            if ($xposServerVersion - $xposCurrentVersion > 0) {
//                $isUpdate = true;
//            }
//            if ((int)$this->getLastCheckUpdate() > 7 && $isUpdate) {
//                $sendMessage = Mage::getSingleton('adminnotification/inbox');
//                $sendMessage->addNotice($data['titleUpdate'], $data['updateInformation'], $data['url'], $data['isInternal']);
//                $this->setLastCheckUpdate();
//            }
//        }
//    }


}
