<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE  {$this->getTable('gearup_sds/history')}  
    ADD `sds_qty` int(11) NULL AFTER `qty`,
    ADD `ext_qty` int(11) NULL AFTER `sds_qty`;
");

$installer->endSetup();