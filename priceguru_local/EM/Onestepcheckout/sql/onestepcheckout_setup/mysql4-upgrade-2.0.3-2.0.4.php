<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
DROP TABLE IF EXISTS {$this->getTable('sales_flat_deliveryorder')};

   CREATE TABLE {$this->getTable('sales_flat_deliveryorder')} (
  `delivery_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50) NOT NULL,
  `real_order_id` int(11) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `telephone` varchar(50) NOT NULL,
  `address` varchar(512) CHARACTER SET utf8 NOT NULL,
  `region` tinyint(4) NULL,
  `product_name` varchar(150) NOT NULL,
  `sku` varchar(150) NOT NULL,
  `qty` smallint(6) NOT NULL DEFAULT '1',
  `attributes` varchar(100) DEFAULT NULL,
  `payment_method` varchar(100) NOT NUll,
  `deposit` int(11) NULL,
  `customer_comment` text CHARACTER SET utf8,
  `delivery_comment` text CHARACTER SET utf8,
  `status` smallint(6) NULL,
  `delivery_time` varchar(50) NULL,
  `order_created_date` DATETIME NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`delivery_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

SQLTEXT;

$installer->run($sql);

$installer->endSetup();
	 