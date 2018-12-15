<?php
$installer = $this;  
$installer->startSetup();
$installer->run("
-- DROP TABLE IF EXISTS {$this->getTable('reward_schedule')};
CREATE TABLE {$this->getTable('reward_schedule')} (
  `reward_schedule_id` int(11) unsigned NOT NULL auto_increment,
  `customer_id` int(11) unsigned NOT NULL,
  `customer_email` varchar(255) NOT NULL default '',
   PRIMARY KEY (`reward_schedule_id`),
   UNIQUE KEY (`customer_id`)
) ");
$installer->endSetup();

?>