<?php

$installer = $this;

$installer->startSetup();

$installer->run ( "
		DROP TABLE IF EXISTS {$this->getTable('salesforce/map')};

		CREATE TABLE IF NOT EXISTS {$this->getTable('salesforce/map')} (
		`id` int(10) unsigned NOT NULL auto_increment COMMENT 'Id',
		`salesforce` varchar(255) DEFAULT NULL COMMENT 'event',
		`magento` varchar(255) DEFAULT NULL COMMENT 'event name',
		`status` varchar(255) DEFAULT NULL COMMENT 'Name',
		`type` varchar(255) DEFAULT NULL ,
		`name` text COMMENT 'Description',
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
		");
$installer->endSetup ();
