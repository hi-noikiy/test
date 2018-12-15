<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT

ALTER TABLE {$this->getTable('sales_flat_deliveryorder')} ADD `pickupid` INT NOT NULL AFTER `real_order_id`;

SQLTEXT;

$installer->run($sql);

$installer->endSetup();
	 