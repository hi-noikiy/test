<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
DROP TABLE IF EXISTS {$this->getTable('sales_invoice_vat')};

  CREATE TABLE {$this->getTable('sales_invoice_vat')} (
  `invoice_vat_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` varchar(50) NOT NULL,
  `pickup_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `sku` varchar(150) NOT NULL,
  `qty` smallint(6) NOT NULL DEFAULT '1',
  `attributes` varchar(100) DEFAULT NULL,
  `vatregno` varchar(50) NULL,
  `brn` varchar(50) NULL,
  `created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`invoice_vat_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

SQLTEXT;

$installer->run($sql);

$installer->endSetup();
	 