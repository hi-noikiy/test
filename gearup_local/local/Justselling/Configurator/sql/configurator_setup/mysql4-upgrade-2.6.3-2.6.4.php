<?php

$this->startSetup();

$this->run("
DROP TABLE IF EXISTS `configurator_list`;
CREATE TABLE `configurator_list` (
  `list_id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `customer_id` int(11) unsigned NOT NULL default '0',
  `created_at` date NOT NULL,
  `is_default` smallint NOT NULL,
  PRIMARY KEY  (`list_id`),
  KEY `IDX_CUSTOMER` (`customer_id`),
  CONSTRAINT `FK_LIST_LIST_CUSTOMER` FOREIGN KEY (`customer_id`) REFERENCES `customer_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `configurator_list_item`;
CREATE TABLE `configurator_list_item` (
  `item_id` int(10) unsigned NOT NULL auto_increment,
  `list_id` int(10) unsigned NOT NULL default '0',
  `product_id` int(10) unsigned NOT NULL default '0',
  `qty` smallint(5) default '0',
  `descr` varchar(255) NOT NULL,
  `buy_request` TEXT NOT NULL,
  PRIMARY KEY  (`item_id`),
  KEY `IDX_LIST` (`list_id`),
  KEY `IDX_PRODUCT` (`product_id`),
  CONSTRAINT `FK_LIST_ITEM_LIST` FOREIGN KEY (`list_id`) REFERENCES `configurator_list` (`list_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_LIST_ITEM_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8;


");

$this->endSetup(); 