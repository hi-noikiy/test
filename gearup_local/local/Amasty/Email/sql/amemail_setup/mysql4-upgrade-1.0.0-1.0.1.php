<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */   
$this->startSetup();

$this->run("
CREATE TABLE `{$this->getTable('amemail/log')}` (
  `log_id` mediumint(8) unsigned NOT NULL auto_increment,
  `customer_id` mediumint(8) unsigned NOT NULL,
  `order_id` mediumint(8) unsigned NOT NULL,
  `sent_date` datetime NOT NULL,
  `code` varchar(255) NOT NULL,
  `txt` text NOT NULL,
  PRIMARY KEY  (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");


$this->endSetup(); 