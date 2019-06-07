<?php
$installer = $this;
$installer->startSetup();

$installer->run("CREATE TABLE `{$installer->getTable('gearup_sds_horder_flag')}` ( `entity_id` INT(11) NOT NULL AUTO_INCREMENT , "
        . "`order_id` INT(11) NOT NULL , "
        . "`all_sds` INT(2) NOT NULL , PRIMARY KEY (`entity_id`)) ENGINE = InnoDB;");

$installer->endSetup();