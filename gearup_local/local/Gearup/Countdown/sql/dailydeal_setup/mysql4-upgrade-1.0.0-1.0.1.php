<?php
$installer = $this;
$installer->startSetup();

$installer->run("
            ALTER TABLE `{$this->getTable('belvg_countdown')}`
            ADD COLUMN `offer_qty` int(11),ADD COLUMN `isset_qty` int(1) ;
");
$installer->endSetup();