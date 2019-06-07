<?php
$installer = $this;
$installer->startSetup();

$installer->run("CREATE TABLE `{$installer->getTable('wicrew_activity_category_random')}` ( `category_random_id` INT(11) NOT NULL AUTO_INCREMENT ,"
        . " `category_id` INT(11) NOT NULL ,"
        . " `random_number` TEXT NOT NULL , PRIMARY KEY (`category_random_id`)) ENGINE = InnoDB;");

$installer->endSetup();