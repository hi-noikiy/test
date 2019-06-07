<?php

$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE `{$installer->getTable('ffdxshippingbox/history')}` ADD `event` VARCHAR(255) NULL DEFAULT NULL AFTER `activity`;");

$installer->endSetup();