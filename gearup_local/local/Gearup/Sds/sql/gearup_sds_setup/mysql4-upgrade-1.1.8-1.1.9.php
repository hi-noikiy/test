<?php

$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE  {$this->getTable('gearup_sds/history')}
    modify `cost` decimal(10,4) NULL,
    modify `cost_value` decimal(10,4) NULL;
");
$installer->endSetup();