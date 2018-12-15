<?php
$installer = $this;
$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('service_center')};
CREATE TABLE {$this->getTable('service_center')} (
  `service_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `service_name` varchar(150) NOT NULL,
  `service_address` text NOT NULL,
  `service_latitude` varchar(150) NOT NULL,
  `service_longitude` varchar(150) NOT NULL,
  `service_status` smallint(6) NOT NULL DEFAULT '1',
  PRIMARY KEY (`service_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

");

$sql=<<<SQLTEXT

ALTER TABLE {$this->getTable('repair_to_center')} ADD `sc_id` int(11) unsigned AFTER `wholesaler`,
    ADD `sc_address` text AFTER `sc_id`, 
    ADD `sc_latitude` varchar(150) AFTER `sc_address`, 
    ADD `sc_longitude` varchar(150) AFTER `sc_latitude`,
    ADD `c_address` text AFTER `sc_longitude`,
    ADD `c_latitude` varchar(150) AFTER `c_address`,
    ADD `c_longitude` varchar(150) AFTER `c_latitude`,
    ADD `dispatch` smallint(6) NOT NULL AFTER `status`,
    ADD `dispatch_date` datetime DEFAULT NULL AFTER `dispatch`
        ;
   
SQLTEXT;

$installer->run($sql);

$installer->endSetup();