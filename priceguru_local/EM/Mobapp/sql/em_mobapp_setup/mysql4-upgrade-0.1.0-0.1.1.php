<?php

/** @var $installer Mage_Catalog_Model_Resource_Setup */
$installer  = $this;

$installer->getConnection()->addColumn(
    $installer->getTable('mobapp/store'),
    'slideshow2',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length'     => '2M',
        'comment'   => 'Iphone/Mobile Smaller Banner'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('mobapp/store'),
    'slideshow3',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length'     => '2M',
        'comment'   => 'Ipad/Tablets large Banner'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('mobapp/store'),
    'slideshow4',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
		'length'     => '2M',
        'comment'   => 'Ipad/Tablets Smaller Banner'
    )
);

$installer->endSetup(); 