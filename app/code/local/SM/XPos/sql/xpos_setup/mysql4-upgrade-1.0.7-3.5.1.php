<?php
$installer = $this;
$installer->startSetup();

$xposHelper = Mage::helper("xpos");

if(!$xposHelper->columnExist($this->getTable('sales/order'), 'x_pos_discount')) {
    $installer->run(" ALTER TABLE {$this->getTable('sales/order')} ADD `x_pos_discount` decimal(12,4) NULL; ");
}

$installer->endSetup();