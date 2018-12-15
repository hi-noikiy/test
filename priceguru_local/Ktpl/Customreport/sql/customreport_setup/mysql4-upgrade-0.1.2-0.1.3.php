<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT

ALTER TABLE {$this->getTable('sales_flat_deliveryorder')} ADD `pickup_status` int(1)  AFTER `delivery_comment`,
     ADD `pickup_comment` text CHARACTER SET utf8 AFTER `pickup_status`,
     ADD `pickup_date` datetime AFTER `pickup_comment`,
     ADD `delivered_by` int(1) AFTER `pickup_comment`, 
     ADD `leadtime` varchar(150) AFTER `status`, 
     ADD `full_leadtime` varchar(150) AFTER `leadtime`; 

SQLTEXT;

$installer->run($sql);

$installer->endSetup();