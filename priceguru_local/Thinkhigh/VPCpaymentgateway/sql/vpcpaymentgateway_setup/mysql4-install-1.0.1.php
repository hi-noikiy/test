<?php

$installer = $this;
$installer->startSetup();

$installer->getConnection()
    ->addColumn($installer->getTable('sales_flat_quote_payment'), 'vpc_card', 'VARCHAR(255) NULL');
$installer->getConnection()
    ->addColumn($installer->getTable('sales_flat_quote_payment'), 'vpc_card_num', 'VARCHAR(255) NULL');
$installer->getConnection()
    ->addColumn($installer->getTable('sales_flat_quote_payment'), 'vpc_card_exp', 'VARCHAR(255) NULL');
$installer->getConnection()
    ->addColumn($installer->getTable('sales_flat_quote_payment'), 'vpc_card_security_code', 'VARCHAR(255) NULL');

$installer->getConnection()
    ->addColumn($installer->getTable('sales_flat_order_payment'), 'vpc_card', 'VARCHAR(255) NULL');
$installer->getConnection()
    ->addColumn($installer->getTable('sales_flat_order_payment'), 'vpc_card_num', 'VARCHAR(255) NULL');
$installer->getConnection()
    ->addColumn($installer->getTable('sales_flat_order_payment'), 'vpc_card_exp', 'VARCHAR(255) NULL');
$installer->getConnection()
    ->addColumn($installer->getTable('sales_flat_order_payment'), 'vpc_card_security_code', 'VARCHAR(255) NULL');
    
$installer->endSetup();
