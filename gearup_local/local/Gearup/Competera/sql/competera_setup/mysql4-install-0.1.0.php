<?php
 /**
 * Magento
 *
 * DISCLAIMER
 *
 * Competera API to compare / update price
 *
 * @category   Gearup
 * @package    Gearup_Competera
 * @author     Gunjan <gunjan@krishtechnolabs.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
$installer->startSetup();

/**
 * Create table 'competera/competerahistory'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('competera/competerahistory'))
    ->addColumn('history_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Id')
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
        ), 'Title')
    ->addColumn('creation_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'History Creation Time')
    ->setComment('Competera History Table');
$installer->getConnection()->createTable($table);

/**
 * Create table 'competera/pricechangelog'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('competera/pricechangelog'))
    ->addColumn('pricechange_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Id')
    ->addColumn('history_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        ), 'History Id')
    ->addIndex($installer->getIdxName('competera/pricechangelog', array('history_id')),
        array('history_id'))
    ->addColumn('creation_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Changelog Creation Time')
    ->addColumn('sku', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
        ), 'Sku')
    ->addColumn('part_number', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
        ), 'Part Number')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
        ), 'Product Name')
    ->addColumn('price_type', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
        ), 'Price Type')
    ->addColumn('old_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'Old Price')
    ->addColumn('new_price', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
        ), 'New Price')
    ->addForeignKey(
        $installer->getFkName(
            'competera/pricechangelog',
            'history_id',
            'competera/competerahistory',
            'history_id'
        ),
        'history_id', $installer->getTable('competera/competerahistory'), 'history_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Competera Price Changelog Table');
$installer->getConnection()->createTable($table);

$installer->endSetup();