<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Celigo\Magento2NetSuiteConnector\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Create celigo_sales_order table.
     *
     * @param Magento\Framework\Setup\SchemaSetupInterface $installer
     * @return void
     */
    public function createCeligoSalesOrderTable($installer)
    {
        // new table celigo_sales_order.
        if (!$installer->getConnection()->isTableExists($installer->getTable('celigo_sales_order'))) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('celigo_sales_order')
            )->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'ID'
            )->addColumn(
                'parent_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Parent Id'
            )->addColumn(
                'is_exported_to_io',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['unsigned' => true, 'default' => 0],
                'Is Exported To IO'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addIndex(
                $installer->getIdxName('celigo_sales_order', ['parent_id']),
                ['parent_id']
            )->addForeignKey(
                $installer->getFkName('celigo_sales_order', 'parent_id', 'sales_order', 'entity_id'),
                'parent_id',
                $installer->getTable('sales_order'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
            )->setComment('Celigo Sales Order table');

            $installer->getConnection()->createTable($table);
        }
    }
}
