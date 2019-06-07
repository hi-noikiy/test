<?php
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer = $this;

$installer->startSetup();
$connection = $installer->getConnection();
/**
 * Drop tables if exist
 */

$connection->dropTable($installer->getTable('hordermanager/period'));
$connection->dropTable($installer->getTable('hordermanager/order'));
$connection->dropTable($installer->getTable('hordermanager/order_item'));

/**
 * Create table 'hordermanager/period'
 */

$table = $installer->getConnection()
    ->newTable($installer->getTable('hordermanager/period'))
    ->addColumn('period_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Block ID')
    ->addColumn('custom_period_id', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Block Custom Editable Period ID')
    ->addColumn('date_from', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'  => false,
    ), 'Period Start')
    ->addColumn('date_to', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'  => false,
    ), 'Period End')
    ->setComment('Hatimeria Order Manager Period Table');
$installer->getConnection()->createTable($table);


/**
 * create table 'hordermanager/period_has_order'
 */

$table = $installer->getConnection()
    ->newTable($installer->getTable('hordermanager/order'))
    ->addColumn('period_has_order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Block ID')
    ->addColumn('period_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Block Period ID')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Block Order ID')
    ->addColumn('estimated_shipping', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
        'nullable'  => false
    ), 'Column with date of estimated date of shipping')
    ->addColumn('is_hidden', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
    ), 'Block If Order Is Hidden')
    ->setComment('Hatimeria Order Manager Period-has-Order Table');
$installer->getConnection()->createTable($table);

/**
 * create table 'hordermanager/period_order_has_item'
 */

$table = $installer->getConnection()
    ->newTable($installer->getTable('hordermanager/order_item'))
    ->addColumn('period_order_has_item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'Block ID')
    ->addColumn('item_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Block Item ID')
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Block Order ID')
    ->addColumn('period_order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'nullable'  => false,
    ), 'Block ID from table hordermanager_period_has_order')
    ->addColumn('ordered', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
    ), 'Block Is Ordered')
    ->addColumn('in_stock', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
    ), 'Block Is In Stock')
    ->addColumn('supplier_notes', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => true,
    ), 'Block Supplier Notes')
    ->addColumn('admin_notes', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => true,
    ), 'Block Admin Notes')

    ->setComment('Hatimeria Order Manager Period-has-Order-has-Item Table');
$installer->getConnection()->createTable($table);

$installer->endSetup();