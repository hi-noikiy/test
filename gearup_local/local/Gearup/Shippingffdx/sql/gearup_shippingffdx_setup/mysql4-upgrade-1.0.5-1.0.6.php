<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE `{$installer->getTable('gearup_shippingffdx_destination')}` ADD `courier_nickname` VARCHAR(255) NULL DEFAULT NULL AFTER `courier_name`;");

$installer->endSetup();