<?php

$installer = Mage::getResourceModel('sales/setup', 'sales_setup');
$installer->startSetup();

$upgradeTables = array('quote');

foreach ($upgradeTables as $table) {
        $installer->addAttribute($table, 'gift_map',
                array('type' => 'text', 'default' => ''));
    }

$installer->endSetup();