<?php
$installer = $this;
$installer->startSetup();
$installer->run("CREATE TABLE `{$installer->getTable('gearup_sds_history')}` ( `history_id` INT(11) NOT NULL AUTO_INCREMENT , "
        . "`product_id` INT(11) NOT NULL , "
        . "`sku` VARCHAR(100) NOT NULL , "
        . "`part_number` VARCHAR(100) NOT NULL , "
        . "`create_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , "
        . "`actions` TEXT NOT NULL , PRIMARY KEY (`history_id`)) ENGINE = InnoDB;");
$installer->endSetup();