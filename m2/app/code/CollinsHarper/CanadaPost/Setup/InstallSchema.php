<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CollinsHarper\CanadaPost\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'ch_canadapost_manifest'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ch_canadapost_manifest')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true,
                'nullable' => false,
                'primary' => true,
                'auto_increment' => true
            ],

            'Entity Id'
        )->addColumn(
            'group_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'group_id'
        )->addColumn(
            'url',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'url'
        )->addColumn(
            'media_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            [],
            'media_type'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            [],
            'status'
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
        )->setComment(
            'Canada Post Manifest Table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ch_canadapost_manifest_link'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ch_canadapost_manifest_link')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true,
                'auto_increment' => true],
            'Entity Id'
        )->addColumn(
            'manifest_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'nullable' => false],
            'manifest_id link field'
        )->addColumn(
            'link',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'url for the manifest'
        )->addIndex(
            $installer->getIdxName('ch_canadapost_manifest_link', ['manifest_id']),
            ['manifest_id']
        )->addForeignKey(
            $installer->getFkName('ch_canadapost_manifest_link', 'manifest_id', 'ch_canadapost_manifest', 'entity_id'),
            'manifest_id',
            $installer->getTable('ch_canadapost_manifest'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Manifest link table'
        );
        $installer->getConnection()->createTable($table);

    /**
         * Create table 'ch_canadapost_office'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ch_canadapost_office')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true,
                'auto_increment' => true],
            'Entity Id'
        )->addColumn(
            'city',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'city'
        )->addColumn(
            'postal_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            6,
            [],
            'postal_code'
        )->addColumn(
            'province',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            2,
            [],
            'province'
        )->addColumn(
            'address',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'address'
        )->addColumn(
            'location',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'location'
        )->addColumn(
            'link',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'link'
        )->addColumn(
            'media_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'media_type'
        )->addColumn(
            'cp_office_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            [],
            'cp_office_id'
        )->addColumn(
            'cp_office_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            [],
            'cp_office_name'
        )->addColumn(
            'bilingual',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => '0'],
            'bilingual'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            [],
            'status'
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
            $installer->getIdxName('ch_canadapost_office', ['postal_code']),
            ['postal_code']
        )->setComment(
            'Canada Post office table'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'ch_canadapost_quote_param'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ch_canadapost_quote_param')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true,
                'auto_increment' => true],
            'Entity Id'
        )->addColumn(
            'magento_quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false],
            'quote link field'
        )->addColumn(
            'signature',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => '0'],
            'signature'
        )->addColumn(
            'coverage',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => '0'],
            'coverage'
        )->addColumn(
            'cp_office_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'unsigned' => true,  'default' => '0'],
            'cp_office_id'
        )->addColumn(
            'card_for_pickup',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => '0'],
            'card_for_pickup'
        )->addColumn(
            'do_not_safe_drop',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => '0'],
            'do_not_safe_drop'
        )->addColumn(
            'leave_at_door',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => '0'],
            'leave_at_door'
        )->addColumn(
            'cod',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => '0'],
            'cod'
        )->addColumn(
            'est_delivery_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['nullable' => false, 'default' => '0'],
            'est_delivery_date'
        )->addColumn(
            'coverage_amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0'],
            'coverage_amount'
        )->addColumn(
            'magento_order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'default' => '0'],
            'magento_order_id'
        )->addColumn(
            'signature',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            1,
            ['nullable' => false, 'default' => '0'],
            'signature'
        )->addIndex(
            $installer->getIdxName('ch_canadapost_quote_param', ['cp_office_id']),
            ['cp_office_id']
        )->addForeignKey(
            $installer->getFkName('ch_canadapost_quote_param', 'magento_quote_id', 'quote', 'entity_id'),
            'magento_quote_id',
            $installer->getTable('quote'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('ch_canadapost_quote_param', 'cp_office_id', 'ch_canadapost_office', 'entity_id'),
            'cp_office_id',
            $installer->getTable('ch_canadapost_office'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Quote Param Cache table'
        );
        $installer->getConnection()->createTable($table);



        /**
         * Create table 'ch_canadapost_return'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ch_canadapost_return')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true,
                'auto_increment' => true],
            'Entity Id'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false],
            'order_id'
        )->addColumn(
            'shipment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false],
            'shipment_id'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            [],
            'status'
        )->addColumn(
            'tracking_pin',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            [],
            'tracking_pin'
        )->addColumn(
            'link',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'link'
        )->setComment(
            'canada post returns table'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'ch_canadapost_return'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ch_canadapost_shipment')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true,
                'auto_increment' => true],
            'Entity Id'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false],
            'order_id'
        )->addColumn(
            'shipment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            64,
            ['unsigned' => true, 'nullable' => false],
            'shipment_id'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            [],
            'status'
        )->addColumn(
            'tracking_pin',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            [],
            'tracking_pin'
        )->addColumn(
            'signature',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            1,
            [],
            'signature'
        )->addColumn(
            'magento_shipment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false],
            'magento_shipment_id'
        )->addColumn(
            'manifest_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false],
            'manifest_id'
        )->addColumn(
            'is_delivered',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            1,
            ['unsigned' => true, 'nullable' => false],
            'is_delivered'
        )->addColumn(
            'is_checked',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            1,
            ['unsigned' => true, 'nullable' => false],
            'is_checked'
        )->addColumn(
            'last_update',
            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
            null,
            ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
            'last_update'
        )->addColumn(
            'update_message',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            1255,
            ['unsigned' => true, 'nullable' => false],
            'update_message'
        )->addColumn(
            'update_errors',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'nullable' => false],
            'update_errors'
        )->addIndex(
            $installer->getIdxName('ch_canadapost_shipment', ['magento_shipment_id']),
            ['magento_shipment_id']
        )->addIndex(
            $installer->getIdxName('ch_canadapost_shipment', ['manifest_id']),
            ['manifest_id']
        )->addForeignKey(
            $installer->getFkName('ch_canadapost_shipment', 'magento_shipment_id', 'sales_shipment', 'entity_id'),
            'magento_shipment_id',
            $installer->getTable('sales_shipment'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->addForeignKey(
            $installer->getFkName('ch_canadapost_shipment', 'manifest_id', 'ch_canadapost_manifest', 'entity_id'),
            'manifest_id',
            $installer->getTable('ch_canadapost_manifest'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'canada post shipment table'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'ch_canadapost_shipment_link'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ch_canadapost_shipment_link')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true,
                'auto_increment' => true],
            'Entity Id'
        )->addColumn(
            'cp_shipment_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'nullable' => false],
            'cp_shipment_id link field'
        )->addColumn(
            'rel',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            [],
            'rel'
        )->addColumn(
            'url',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'url'
        )->addColumn(
            'media_type',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            [],
            'media_type'
        )->addIndex(
            $installer->getIdxName('ch_canadapost_shipment_link', ['cp_shipment_id']),
            ['cp_shipment_id']
        )->addForeignKey(
            $installer->getFkName('ch_canadapost_shipment_link', 'cp_shipment_id', 'ch_canadapost_shipment', 'entity_id'),
            'cp_shipment_id',
            $installer->getTable('ch_canadapost_shipment'),
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
        )->setComment(
            'Manifest link table'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
