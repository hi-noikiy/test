<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT

ALTER TABLE {$this->getTable('sales_flat_pickuporder')} ADD `pickup_done` int(1)  AFTER `pickup_by`; 
ALTER TABLE {$this->getTable('sales_flat_deliveryorder')} ADD `client_connected` int(1)  AFTER `delivery_comment`; 

SQLTEXT;

$installer->run($sql);

$installer->endSetup();