<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE `{$installer->getTable('gearup_shippingffdx_tracktype')}` ADD `ref_tracking_number` VARCHAR(30) NULL DEFAULT NULL AFTER `type`;");

$installer->endSetup();