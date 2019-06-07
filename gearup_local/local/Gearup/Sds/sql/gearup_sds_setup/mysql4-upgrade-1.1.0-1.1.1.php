<?php
$installer = $this;
$installer->startSetup();
$installer->run("CREATE TABLE `{$installer->getTable('gearup_invoice_history')}` ( `history_id` INT(11) NOT NULL AUTO_INCREMENT , "
        . "`invoice_id` VARCHAR(100) NOT NULL , "
        . "`create_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP , "
        . "`actions` TEXT NOT NULL , "
        . "`record_by` VARCHAR(100) NOT NULL , PRIMARY KEY (`history_id`)) ENGINE = InnoDB;");
$installer->endSetup();