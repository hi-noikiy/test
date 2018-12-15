<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT

ALTER TABLE {$this->getTable('sales_flat_invoice_grid')} ADD `username` varchar(50)  AFTER `billing_name` ;

SQLTEXT;

$installer->run($sql);

$installer->endSetup();