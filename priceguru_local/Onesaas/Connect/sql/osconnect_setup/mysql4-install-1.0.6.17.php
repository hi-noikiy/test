<?php
$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS {$this->getTable('osconnectkeys')} (
  `key_id` int(10) unsigned NOT NULL auto_increment,
  `key` varchar(250) NOT NULL default '',
  `creation_date` datetime NOT NULL default '0000-00-00',
  PRIMARY KEY  (`key_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
  ");
$installer->run("
INSERT INTO {$this->getTable('osconnectkeys')} VALUES (null,CONCAT(MD5(NOW()), MD5(CURTIME())), NOW());
");
$installer->run("
INSERT INTO {$this->getTable('osconnectkeys')} VALUES (null,'https://secure.onesaas.com/signin/start', NOW());
");
$installer->endSetup();
?>
