<?php

/**
 * Created by PhpStorm.
 * User: SMART
 * Date: 10/15/2015
 * Time: 1:41 PM
 */
class SM_XMessage_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $_secretKey = "jUJaHSy7dfIRlijPwG7EoFutzHcxaT1p";


    public $messageType = array(
        'critical' => 1,
        'major'    => 1,
        'minor'    => 1,
        'notice'   => 1,
    );

    private $_pathServerSendInfo = 'http://xmessages.smartosc.com/api/v1/clients';
    private $_pathServerSendMessage = 'http://xmessages.smartosc.com/api/v1/messages';

    private $_pathTestServerSendInfo = 'http://xmessage.xd.smartosc.com/api/v1/clients';
    private $_pathTestServerSendMessage = 'http://xmessage.xd.smartosc.com/api/v1/messages';

    private $_filePath = 'infosite';
    private $_xIDList = array(
        'SM_XPos'       => '10001',
        'SM_XPosAPI'    => '10007',
        'SM_XB2b'       => '10003',
        'SM_XWarehouse' => '10004',
        'SM_RMA'        => '10005',
        'SM_XMessage'   => '10010',
        'SM_XReport'    => '10008',
        'SM_XPayment'   => '10009',
        'SM_XV'         => '10006',
    );

    private $_waitOnFailure = 900; //15mins

    public function getInfoFile()
    {
        $dir = Mage::getBaseDir('app') . DS . 'code' . DS . 'local' . DS . 'SM' . DS . 'XMessage' . DS . 'etc' . DS;
        return $dir . $this->_filePath;
    }

    /**
     * @return array
     */
    public function getXIDList()
    {
        return $this->_xIDList;
    }

    /**
     * @return string
     */
    public function getPathServerSendInfo()
    {
//        return $this->_pathTestServerSendInfo;
        return $this->_pathServerSendInfo;
    }

    /**
     * @return string
     */
    public function getPathServerSendMessage()
    {
//        return $this->_pathTestServerSendMessage;
        return $this->_pathServerSendMessage;
    }

    /**
     * @return array
     */
    public function getMessageType()
    {
        return $this->messageType;
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->_secretKey;
    }

    /**
     * @return int
     */
    public function getWaitOnFailure()
    {
        return $this->_waitOnFailure;
    }

}