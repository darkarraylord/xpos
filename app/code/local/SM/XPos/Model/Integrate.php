<?php

/**
 * Created by PhpStorm.
 * User: vjcspy
 * Date: 8/28/15
 * Time: 11:02 AM
 */
class SM_XPos_Model_Integrate extends Mage_Core_Model_Abstract {

    /**
     * TODO: Refactor code. Now, all source code to integrate with another extension will be in the directory Integrate
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws Varien_Exception
     */
    public function __call($method, $args) {

        $model = $this->getModelIntegrate();
        if (method_exists($model, $method)) {
            return call_user_func_array(array($model, $method), $args);
        }

        return parent::__call($method, $args);
    }

    /**
     * @return SM_XPos_Model_Integrate_GiftCardAndRewardPoint_Integrate
     */
    private function getModelIntegrate() {
        return Mage::getSingleton('xpos/integrate_giftCardAndRewardPoint_integrate');
    }
}
