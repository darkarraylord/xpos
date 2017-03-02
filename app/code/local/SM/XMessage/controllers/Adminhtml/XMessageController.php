<?php

/**
 * Created by PhpStorm.
 * User: SMART
 * Date: 10/15/2015
 * Time: 11:52 AM
 */
class SM_XMessage_Adminhtml_XMessageController extends Mage_Adminhtml_Controller_Action
{

    public function indexAction()
    {
        $this->loadLayout()->renderLayout();
    }

    public function sendMessageAction()
    {
        $params = $this->getRequest()->getParams();
        $message = array(
            'send_from_name'  => $params['name'],
            'send_from_email' => $params['email'],
            'content'         => $params['content'],
            'type'         => $params['type'],
            'client_id'       => $params['client_id'],
        );
        //set POST variables
        $url = Mage::helper('xmessage')->getPathServerSendMessage();

        //url-ify the data for the POST
        $str = http_build_query($message);

        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($message));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);

        //execute post
        $result = curl_exec($ch);

        //close connection
        curl_close($ch);
    }
}