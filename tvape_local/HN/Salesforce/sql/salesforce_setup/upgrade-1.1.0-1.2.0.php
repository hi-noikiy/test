<?php

$installer = $this;

$installer->startSetup();

$installer->run ( "
		DROP TABLE IF EXISTS {$this->getTable('salesforce/report')};

		CREATE TABLE {$this->getTable('salesforce/report')} (
		`id` int(12) unsigned NOT NULL auto_increment COMMENT 'Id',
		`record_id` varchar(20) DEFAULT NULL ,
		`action` varchar(50) DEFAULT NULL ,
		`table` mediumtext DEFAULT NULL COMMENT 'Salesforce Field',
		`username` varchar(255) DEFAULT NULL COMMENT 'User Name',
		`email` varchar(50) DEFAULT NULL COMMENT 'User Email',
		`datetime` datetime DEFAULT NULL COMMENT 'Datetime create',
		`status` int(2) DEFAULT NULL COMMENT 'status',		
		PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
		");
$installer->endSetup ();