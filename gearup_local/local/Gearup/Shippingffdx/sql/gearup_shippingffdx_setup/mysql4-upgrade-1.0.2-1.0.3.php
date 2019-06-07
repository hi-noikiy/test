<?php

$installer = $this;

$installer->startSetup();

$installer->run("CREATE TABLE `{$installer->getTable('gearup_shippingffdx_destination')}` ( `destination_id` INT(11) NOT NULL AUTO_INCREMENT ,"
. " `destination` VARCHAR(100) NOT NULL ,"
. " `code` VARCHAR(15) NOT NULL ,"
. " `number` VARCHAR(30) NOT NULL, PRIMARY KEY (`destination_id`)) ENGINE = InnoDB;");

$installer->endSetup();