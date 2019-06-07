<?php
$installer = $this;

$installer->startSetup();

$installer->run("CREATE TABLE `{$installer->getTable('gearup_shippingffdx_tracktype')}` ( `shippingffdx_id` INT(11) NOT NULL AUTO_INCREMENT ,"
. " `tracking_number` VARCHAR(30) NOT NULL ,"
. " `type` INT(2) NOT NULL, PRIMARY KEY (`shippingffdx_id`)) ENGINE = InnoDB;");

$installer->endSetup();