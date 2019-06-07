<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();
$connection = $installer->getConnection();

/**
 * drop tables if exists
 */
$connection->dropTable($installer->getTable('ffdxshippingbox/tracking'));

$connection->dropTable($installer->getTable('ffdxshippingbox/history'));

/**
* Create table 'ffdxshippingbox_tracking'
*/

$table = $installer->getConnection()
    ->newTable($installer->getTable('ffdxshippingbox/tracking'))
        ->addColumn('tracking_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Block ID')
        ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Block Order ID')
        ->addColumn('shipment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'Block Shipment ID')
        ->addColumn('tracking_number', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        ), 'Block Tracking Number')
        ->addColumn('checked', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
            'nullable'  => false,
            'default' => 0,
        ), 'Block Status: 0 = still checking, 1 = stop checking')
        ->setComment('FFDX ShippingBox Block Tracking Table');

$installer->getConnection()->createTable($table);

/**
 * Create table 'ffdxshippingbox_tracking_history'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('ffdxshippingbox/history'))
    ->addColumn('history_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Block ID')
    ->addColumn('tracking_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Block Tracking ID')
    ->addColumn('activity', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
    ), 'Block Activity')
    ->addColumn('location', Varien_Db_Ddl_Table::TYPE_VARCHAR, 16, array(
        'nullable'  => false,
    ), 'Block Location')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    ), 'Block Date')
    ->setComment('FFDX ShippingBox Block Tracking History Table');

$installer->getConnection()->createTable($table);

$installer->endSetup();