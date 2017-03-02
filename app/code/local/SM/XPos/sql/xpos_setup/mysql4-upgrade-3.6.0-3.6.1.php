<?php
$installer = $this;
$installer->startSetup();

$xposHelper = Mage::helper("xpos");

if (!$xposHelper->columnExist($this->getTable('xpos/user'), 'pin_code')) {
    $installer->run(" ALTER TABLE {$this->getTable('xpos/user')} ADD `pin_code` varchar(120) NULL; ");
}

if (!$xposHelper->columnExist($this->getTable('sales/order_item'), 'warehouse_id')){
    $installer->run(" ALTER TABLE {$this->getTable('sales/order_item')} ADD `warehouse_id` smallint(5); ");
}

if (!$xposHelper->columnExist($this->getTable('sales/order'), 'warehouse_id')){
    $installer->run(" ALTER TABLE {$this->getTable('sales/order')} ADD `warehouse_id` smallint(5); ");
}

$installer->endSetup();
