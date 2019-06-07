<?php

$installer = $this;

$installer->startSetup();

$installer->run("CREATE TABLE `{$installer->getTable('gearup_shippingffdx_history')}` ( `history_id` int(11) NOT NULL AUTO_INCREMENT,
  `track_id` varchar(100) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actions` text NOT NULL,
  `record_by` varchar(100) DEFAULT NULL, PRIMARY KEY (`history_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

$installer->endSetup();