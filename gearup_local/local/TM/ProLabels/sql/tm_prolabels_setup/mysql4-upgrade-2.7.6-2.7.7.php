<?php
$installer = $this;

$installer->startSetup();

$installer->getConnection()->changeColumn(
        $installer->getTable('prolabels/label'),
        'product_image_text',
        'product_image_text',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable'  => true,
        )
    );


$installer->getConnection()->changeColumn(
        $installer->getTable('prolabels/label'),
        'category_image_text',
        'category_image_text',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable'  => true,
        )
    );

$installer->getConnection()->changeColumn(
        $installer->getTable('prolabels/system'),
        'product_image_text',
        'product_image_text',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable'  => true,
        )
    );


$installer->getConnection()->changeColumn(
        $installer->getTable('prolabels/system'),
        'category_image_text',
        'category_image_text',
        array(
            'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
            'nullable'  => true,
        )
    );

$installer->endSetup();
