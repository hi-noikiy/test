<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT

ALTER TABLE {$this->getTable('sales_flat_deliveryorder')} ADD `latitude` varchar(150) AFTER `address`,
    ADD `longitude` varchar(150) AFTER `latitude`; 

SQLTEXT;

$installer->run($sql);

$installer->endSetup();