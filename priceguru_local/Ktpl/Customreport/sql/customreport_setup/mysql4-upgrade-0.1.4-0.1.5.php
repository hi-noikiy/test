<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT

ALTER TABLE {$this->getTable('sales_flat_deliveryorder')} ADD `del_time2` varchar(150) AFTER `del_time`,
    ADD `order_status` varchar(150) AFTER `status`; 

SQLTEXT;

$installer->run($sql);

$installer->endSetup();