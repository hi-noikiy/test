<?php
$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('ordercustomer')};
CREATE TABLE {$this->getTable('ordercustomer')} (
  `ordercustomer_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `increment_id` int(11) NOT NULL,
  `order_created_date` datetime DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `product_name` varchar(512) NOT NULL,
  `product_subtitle` varchar(255) NOT NULL,
  `customtitle` varchar(255) NOT NULL,
  `product_sku` varchar(255) NOT NULL,
  `price` decimal(12,4) NOT NULL default '0',
  `payment_type` varchar(255) NOT NULL,
  `invoice_comment` text NULL,
  `created_time` datetime DEFAULT NULL,
  PRIMARY KEY (`ordercustomer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

");

$installer->endSetup();