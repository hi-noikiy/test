<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT

ALTER TABLE {$this->getTable('sales_flat_pickuporder')} ADD `pickup_by` varchar(50)  AFTER `pickup`,
    ADD `pickup_date` DATETIME AFTER `pickup_by`,
    ADD `delivery_date` datetime AFTER `delivery`,
    ADD `client_connected` tinyint(4) NOT NULL DEFAULT '0' AFTER `status`;

SQLTEXT;

$installer->run($sql);

$installer->endSetup();