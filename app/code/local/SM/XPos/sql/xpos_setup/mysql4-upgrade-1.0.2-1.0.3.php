<?php
$installer = $this;
$installer->startSetup();

$xposHelper = Mage::helper("xpos");

if(!$xposHelper->columnExist($this->getTable('sales/order'), 'till_id')) {
    $installer->run(" ALTER TABLE {$this->getTable('sales/order')} ADD `till_id` int( 2 ) unsigned NULL; ");
}

    $categories = Mage::getModel('catalog/category')
        ->getCollection()
        ->addAttributeToSelect('*')
        ->addIsActiveFilter();
    foreach ($categories as $cata) {
        if ($cata->getParentCategory()->getId() == 2) {
            //echo $cata->getId() . ':' . $cata->getName() . '</br>';
            $cata->setData('xpos_enable', 1)->setData('xpos_name',$cata->getName())->save();
        }
    }
$installer->endSetup();
