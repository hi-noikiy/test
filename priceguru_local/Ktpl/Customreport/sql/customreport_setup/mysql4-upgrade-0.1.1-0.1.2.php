<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
DROP TABLE IF EXISTS {$this->getTable('po_order')};

  CREATE TABLE {$this->getTable('po_order')} (
  `po_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
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
  `wholesale_price` int(11) UNSIGNED NULL,
  `retail_price` int(11) NOT NULL,
  `markup` varchar(50) NULL,
  `wholesaler_id` int(11) NULL,
  `pickup_address` text CHARACTER SET utf8,
  `po_comment` text CHARACTER SET utf8,
  `inventory` tinyint(4) NULL,
  `status` smallint(6) NULL,
  `leadtime` varchar(150),
  `order_created_date` DATETIME NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`po_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

ALTER TABLE {$this->getTable('sales_flat_pickuporder')} ADD `po_id` int(11)  AFTER `real_order_id`,
     ADD `po_comment` text CHARACTER SET utf8 AFTER `pickup_comment`,
     ADD `leadtime` varchar(150) AFTER `status`; 

SQLTEXT;

$installer->run($sql);

$installer->endSetup();