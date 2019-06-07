<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE  {$this->getTable('gearup_sds/history')}  
    ADD `qty` int(11) NULL AFTER `actions`,
    ADD `in_out` varchar(255) NULL AFTER `qty`,
    ADD `sds_status` BOOLEAN NULL AFTER `in_out`;
");

$installer->endSetup();