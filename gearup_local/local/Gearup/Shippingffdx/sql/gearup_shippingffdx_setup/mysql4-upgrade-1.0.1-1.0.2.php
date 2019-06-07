<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE `{$installer->getTable('ffdxshippingbox_tracking')}` ADD `created_at` TIMESTAMP NULL AFTER `checked`;");

$installer->endSetup();