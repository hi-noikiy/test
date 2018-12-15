<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
DROP TABLE IF EXISTS {$this->getTable('sales_flat_cimorder')};

   CREATE TABLE {$this->getTable('sales_flat_cimorder')} (
  `cimorder_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `telephone` varchar(50) NOT NULL,
  `email` varchar(100) CHARACTER SET utf8 NOT NULL,
  `iscimcustomer` tinyint(4) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `sku` varchar(150) NOT NULL,
  `attributes` varchar(100) DEFAULT NULL,
  `dcp` int(11) UNSIGNED NULL,
  `installments` varchar(100) NOT NUll,
  `monthly` int(11) NOT NULL,
  `deposit` int(11) NULL,
  `cpp` tinyint(4) NULL,
  `payment` varchar(100) NULL,
  `app_number` varchar(100) NULL,
  `cimcomment` text CHARACTER SET utf8,
  `pgcomment` text CHARACTER SET utf8,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`cimorder_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

SQLTEXT;

$installer->run($sql);

$installer->endSetup();
	 