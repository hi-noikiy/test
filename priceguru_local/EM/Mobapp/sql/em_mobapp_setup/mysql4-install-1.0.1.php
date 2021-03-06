<?php

$installer = $this;

$installer->startSetup();

/**
 * Create table 'mobapp/slider'
 */
if(!$installer->tableExists($installer->getTable('mobapp/store'))){
$table = $installer->getConnection()
    ->newTable($installer->getTable('mobapp/store'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Mobapp Store ID')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
        ), 'Mobapp Store name')
	->addColumn('license', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
        ), 'Mobapp Store license')
	->addColumn('start', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Mobapp start date')
    ->addColumn('expired', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Mobapp expired date')
	->addColumn('status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'nullable'  => false,
        'default'   => '0',
        ), 'Is Mobapp Active')
	->addColumn('slideshow', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(
        ), 'Iphone/Mobile large Banner')
	->addColumn('slideshow2', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(
        ), 'Iphone/Mobile Smaller Banner')
	->addColumn('slideshow3', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(
        ), 'Ipad/Tablets large Banner')
	->addColumn('slideshow4', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(
        ), 'Ipad/Tablets Smaller Banner')
	->addColumn('theme', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(
        ), 'theme params')
	->addColumn('color', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
        ), 'color app')
	->addColumn('paygate', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(
	), 'paygate params')
	->addColumn('creation_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Mobapp Creation Time')
    ->addColumn('update_time', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Mobapp Modification Time')
    ->setComment('EM Mobapp Store Table');
$installer->getConnection()->createTable($table);
}

$installer->endSetup();