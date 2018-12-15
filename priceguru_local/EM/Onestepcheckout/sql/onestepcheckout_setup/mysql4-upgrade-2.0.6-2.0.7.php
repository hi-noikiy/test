<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT

ALTER TABLE {$this->getTable('sales_flat_deliveryorder')} ADD `delivery_date_time` DATETIME NULL AFTER `delivery_time`;

SQLTEXT;

$installer->run($sql);

$installer->endSetup();
	 