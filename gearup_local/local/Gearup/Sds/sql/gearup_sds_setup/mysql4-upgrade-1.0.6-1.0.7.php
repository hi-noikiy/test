<?php

$installer = $this;

$installer->startSetup();
$installer->run("CREATE TABLE `{$installer->getTable('gearup_sds_horder')}` ( `entity_id` INT(11) NOT NULL AUTO_INCREMENT ,"
. " `product_id` INT(11) NOT NULL , PRIMARY KEY (`entity_id`)) ENGINE = InnoDB;");

$installer->endSetup();


