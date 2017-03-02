<?php

/**
 * Created by PhpStorm.
 * User: SMART
 * Date: 10/15/2015
 * Time: 12:15 PM
 */
class SM_XMessage_Model_Observer extends Mage_Core_Model_Abstract
{


    protected $_filePath = '';
    protected $_xList = array();
    protected $_domain = '';
    protected $_waitOnFailure;
    const XML_PATH_INFO = 'xmessage/general/info';

    public function __construct()
    {
        $this->_filePath = Mage::helper('xmessage')->getInfoFile();
        $this->_xList = Mage::helper('xmessage')->getXIDList();
        $this->_domain = trim(str_replace('index.php', '', Mage::getBaseUrl()), '/');
        $this->_waitOnFailure = Mage::helper('xmessage')->getWaitOnFailure();
    }

    public function init($observer)
    {
        $action = $observer->getEvent()->getAction();
        $actionName = $action->getFullActionName();
        if ($actionName == 'xmessage_index_index') {
            $this->getMessageFromServer(Mage::app()->getFrontController()->getRequest()->getParams());
        }else if(strpos($actionName,'adminhtml_') !== false){
            $this->checkInfo($observer);
        }
    }

    protected function shouldCheckInfoSite($data)
    {
        if (! empty($data['last_request']) && time() - $data['last_request'] < $this->_waitOnFailure) {
            return false;
        }

        return true;
    }

    protected function readFileInfoSite()
    {
        $config = Mage::getModel('core/config_data')
            ->load(self::XML_PATH_INFO, 'path')->getValue();
        return json_decode($config, true);
    }

    protected function writeFileInfoSite($data = array())
    {
        if (! is_array($data)) {
            $data = array();
        }
        $data['last_request'] = time();
        Mage::getModel('core/config_data')
            ->load(self::XML_PATH_INFO, 'path')
            ->setValue(json_encode($data))
            ->setPath(self::XML_PATH_INFO)
            ->save();
    }

    /**
     * @param $observer
     */
    public function checkInfo($observer)
    {

        $json = $this->readFileInfoSite();

        // check time
        if (! $this->shouldCheckInfoSite($json)) {
            return;
        }

        $result = $this->checkModule($json);
        if ($result['hasChange']) {
            $this->registerInfoToServer($result['data']);
        }
    }

    protected function checkModule($json)
    {
        //$modules = Mage::getConfig()->getNode('modules')->children();
        //$modulesArray = (array)$modules;
        $data = array();
        $hasChange = false;

        if (! $json) {
            $hasChange = true;
        }

        foreach ($this->_xList as $k => $item) {
            $jsonVersion = null;
            if (isset($json['apps'][$item])) {
                $jsonVersion = $json['apps'][$item];
            }

            $extensionVersion = (array) Mage::getConfig()->getModuleConfig($k)->version;
            $extensionVersion = isset($extensionVersion[0]) ? $extensionVersion[0] : null;

            if ($jsonVersion != $extensionVersion) {
                $hasChange = true;
            }

            if ($extensionVersion !== null) {
                $data[$item] = $extensionVersion;
            }

        }

        return array('hasChange' => $hasChange, 'data' => $data);
    }

    public function getMessageFromServer($params)
    {
        $data = array(
            'title'       => $params['title'],
            'information' => $params['content'],
            'url'         => $params['link'],
            'type'        => $params['type'],
        );

        $this->validateRequest($data);
        $this->addMesssage($data['type'], $data['title'], $data['information'], $data['url']);

        http_response_code(200);
        echo $this->getClientId();
        die;

    }

    protected function getClientId()
    {
        $json = json_decode(file_get_contents($this->_filePath));
        if (count($json) > 0) {
            return $json->client_id;
        }
    }

    protected function addMesssage($type, $title, $info, $url)
    {
        $sendMessage = Mage::getSingleton('adminnotification/inbox');
        $sendMessage->{'add' . ucfirst($type)}($title, $info, $url, true);
    }

    protected function validateRequest(array $data)
    {
        $messageType = Mage::helper('xmessage')->getMessageType();
        if (filter_var($data['url'], FILTER_VALIDATE_URL) === false) {
            $this->response400('invalid url');
        }
        if (strlen($data['information']) == 0) $this->response400('Content is required');
        if (strlen($data['title']) == 0) $this->response400('Title is required');
        if (!isset($messageType[$data['type']])) $this->response400('Invalid type');
    }

    protected function registerInfoToServer($data)
    {

        $salt = mt_rand();
        $signature = hash_hmac('sha256', $salt, Mage::helper('xmessage')->getSecretKey(), true);
        $encodedSignature = base64_encode($signature);

        $url = Mage::helper('xmessage')->getPathServerSendInfo();
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,5);
        $request['salt'] = $salt;
        $request['signature'] = $encodedSignature;
        $request['domain'] = $this->_domain;

        foreach ($data as $appId => $version) {
            $request['apps'][] = array('app_id' => $appId, 'version' => $version);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request));
        $buffer = json_decode(curl_exec($ch));
        if (empty($buffer->id)) {
            $content = $this->readFileInfoSite();
        } else {
            $content['apps'] = $data;
            $content['client_id']=$buffer->id;
        }

        $this->writeFileInfoSite($content);

        curl_close($ch);
    }

    protected function response400($message)
    {
        header('HTTP/1.1 400 Bad request', true, 400);
        header('Content-Type: application/json');
        echo json_encode(array('message' => $message));
        die;
    }

}