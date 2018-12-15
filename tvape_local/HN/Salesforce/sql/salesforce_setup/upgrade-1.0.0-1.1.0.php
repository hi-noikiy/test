<?php

$installer = $this;

$installer->startSetup();

$installer->run ( "
		DROP TABLE IF EXISTS {$this->getTable('salesforce/field')};

		CREATE TABLE IF NOT EXISTS {$this->getTable('salesforce/field')} (
		`id` int(10) unsigned NOT NULL auto_increment COMMENT 'Id',
		`type` varchar(255) DEFAULT NULL ,
		`salesforce` mediumtext DEFAULT NULL COMMENT 'Salesforce Field',
		`magento` mediumtext DEFAULT NULL COMMENT 'Magento Field',
		`status` int(2) DEFAULT NULL COMMENT 'status',		
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
		");
$installer->endSetup ();