<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE `{$installer->getTable('gearup_shippingffdx_destination')}` ADD `courier_name` VARCHAR(255) NULL DEFAULT NULL AFTER `destination_id`,"
. " ADD `tracking_url` VARCHAR(255) NULL DEFAULT NULL AFTER `number` ;");

$installer->endSetup();