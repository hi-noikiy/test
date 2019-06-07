<?php

$installer = $this;

$installer->startSetup();

$installer->run("CREATE TABLE `{$installer->getTable('gearup_sds_tracking')}` ( `sds_tracking_id` INT(11) NOT NULL AUTO_INCREMENT ,"
. " `product_id` INT(11) NOT NULL ,"
. " `update_last_at` DATETIME NOT NULL ,"
. " `order_id` INT(11) NULL DEFAULT NUL , PRIMARY KEY (`sds_tracking_id`)) ENGINE = InnoDB;");

$installer->endSetup();


