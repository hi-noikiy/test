<?php
$installer = $this;
$installer->startSetup();

$installer->run("CREATE TABLE `{$installer->getTable('wicrew_activity_record')}` ( `entity_id` INT(11) NOT NULL AUTO_INCREMENT ,"
        . " `product_id` INT(11) NOT NULL ,"
        . " `number_count` INT(5) NOT NULL ,"
        . " `type` INT(2) NOT NULL ,"
        . " `created_at` DATETIME NULL , PRIMARY KEY (`entity_id`)) ENGINE = InnoDB;");

$installer->run("CREATE TABLE `{$installer->getTable('wicrew_activity_expire')}` ( `entity_id` INT(11) NOT NULL AUTO_INCREMENT ,"
        . " `expire_at` DATETIME NULL , PRIMARY KEY (`entity_id`)) ENGINE = InnoDB;");

$installer->endSetup();