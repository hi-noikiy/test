<?php

/*Ticket5492 - DXB Storage Manager - Report In/Out
-- Add two filed into cost and cost value */
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE  {$this->getTable('gearup_sds/history')}
    ADD `cost` decimal(10,0),
    ADD `cost_value` decimal(10,0)
");
$installer->endSetup();