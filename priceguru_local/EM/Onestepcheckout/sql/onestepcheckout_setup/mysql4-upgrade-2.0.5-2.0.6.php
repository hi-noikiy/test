<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT

  ALTER TABLE {$this->getTable('sales_flat_deliveryorder')} CHANGE `status` `status` SMALLINT(6) NOT NULL DEFAULT  '0';

SQLTEXT;

$installer->run($sql);

$installer->endSetup();
	 