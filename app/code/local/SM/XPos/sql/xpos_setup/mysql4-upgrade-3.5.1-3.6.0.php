<?php
$installer = $this;
$installer->startSetup();

$xposHelper = Mage::helper("xpos");

if (!$xposHelper->columnExist($this->getTable('xpos/user'), 'auto_login')) {
    $installer->run(" ALTER TABLE {$this->getTable('xpos/user')} ADD `auto_login` int( 5 ) NULL; ");
}
if (!$xposHelper->columnExist($this->getTable('xpos/user'), 'store_id')) {
    $installer->run(" ALTER TABLE {$this->getTable('xpos/user')} ADD `store_id` VARCHAR( 255 ) NULL; ");
}
if (!$xposHelper->columnExist($this->getTable('xpos/till'), 'store_id')) {
    $installer->run(" ALTER TABLE {$this->getTable('xpos/till')} ADD `store_id` VARCHAR( 255 ) NULL; ");
}
$installer->endSetup();
