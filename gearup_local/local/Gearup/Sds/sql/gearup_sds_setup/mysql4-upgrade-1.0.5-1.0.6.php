<?php

$installer = $this;

$installer->startSetup();
$installer->run("ALTER TABLE `{$installer->getTable('gearup_sds_tracking')}` ADD `inbound` INT(11) NULL DEFAULT NULL AFTER `order_id`;");

$installer->endSetup();


