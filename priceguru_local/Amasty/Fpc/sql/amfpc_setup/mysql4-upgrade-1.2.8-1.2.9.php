<?php
/**
 * @copyright  Amasty (http://www.amasty.com)
 */
$this->startSetup();

$this->run("
CREATE TABLE `{$this->getTable('amfpc/url')}` (
  `url_id`       int(10) unsigned NOT NULL auto_increment,
  `url`          varchar(255) NOT NULL,
  `rate`         int(11) unsigned NOT NULL,

  PRIMARY KEY (`url_id`),
  UNIQUE KEY (`url`)
) ENGINE=InnoDB;
");

$this->endSetup();
