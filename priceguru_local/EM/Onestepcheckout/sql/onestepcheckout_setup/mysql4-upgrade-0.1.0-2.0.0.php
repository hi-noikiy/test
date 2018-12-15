<?php
$installer = $this;

$installer->startSetup();

/**
 * create rewardpointsrule table
 */
$installer->getConnection()->addColumn($this->getTable('sales/order'), 'iscimorder', 'SMALLINT NOT NULL DEFAULT 0');

$installer->endSetup();
	 