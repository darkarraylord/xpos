<?php
/**
 * Created by PhpStorm.
 * User: Huy
 * Date: 6/29/2015
 * Time: 4:42 PM
 */

class SM_XPos_Block_Adminhtml_XPos_CreditMemo_Order extends SM_XPos_Block_Adminhtml_XPos_CreditMemo_Abstract {
    public function _construct() {
        $this->setTemplate('sm/xpos/creditmemo/order.phtml');
    }
}
