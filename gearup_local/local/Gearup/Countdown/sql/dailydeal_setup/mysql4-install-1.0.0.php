<?php

$installer = $this;
$installer->startSetup();
$installer->run("
            ALTER TABLE `{$this->getTable('belvg_countdown')}` 
            ADD COLUMN `offer_price` decimal(12,4);
");
$installer->endSetup(); 
