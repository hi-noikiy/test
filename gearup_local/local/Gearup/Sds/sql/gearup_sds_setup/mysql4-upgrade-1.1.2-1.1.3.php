<?php
$installer = $this;
$installer->startSetup();
//$installer->run("CREATE TABLE `{$installer->getTable('gearup_sales_commentby')}` ( `commentby_id` INT(11) NOT NULL AUTO_INCREMENT ,"
//    . " `parent_id` INT(11) NOT NULL ,"
//    . " `user` VARCHAR(100) NOT NULL ,"
//    . " `status` INT(2) NOT NULL , PRIMARY KEY (`commentby_id`)) ENGINE = InnoDB;");
$installer->run("ALTER TABLE `{$installer->getTable('sales_flat_order_status_history')}` ADD `user` VARCHAR(100) NULL AFTER `entity_name`;");
$installer->endSetup();