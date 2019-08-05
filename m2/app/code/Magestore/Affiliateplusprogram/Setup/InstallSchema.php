<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Affiliateplusprogram\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * schema table
     */
    const SCHEMA_PROGRAM_PROGRAM =      'magestore_affiliateplusprogram';
    const SCHEMA_PROGRAM_VALUE =        'magestore_affiliateplusprogram_value';
    const SCHEMA_PROGRAM_TRANSACTION =  'magestore_affiliateplusprogram_transaction';
    const SCHEMA_PROGRAM_ACCOUNT =      'magestore_affiliateplusprogram_account';
    const SCHEMA_PROGRAM_PRODUCT =      'magestore_affiliateplusprogram_product';
    const SCHEMA_PROGRAM_CATEGORY =     'magestore_affiliateplusprogram_category';
    const SCHEMA_PROGRAM_JOINED =       'magestore_affiliateplusprogram_joined';

    const SCHEMA_ACCOUNT =              'magestore_affiliateplus_account';
    const SCHEMA_TRANSACTION =          'magestore_affiliateplus_transaction';
    const SCHEMA_BANNER =               'magestore_affiliateplus_banner';
    const SCHEMA_CATALOG_PRODUCT =      'catalog_product_entity';
    const SCHEMA_CATALOG_CATEGORY =     'catalog_category_entity';


    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_PROGRAM_PROGRAM));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_PROGRAM_VALUE));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_PROGRAM_TRANSACTION));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_PROGRAM_ACCOUNT));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_PROGRAM_PRODUCT));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_PROGRAM_CATEGORY));



        /**
         * Create table 'magestore_affiliateplusprogram'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_PROGRAM_PROGRAM))
            ->addColumn(
                'program_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Program Id'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Program Name'
            )->addColumn(
                'created_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                255,
                ['nullable' => false, 'default' => '0000-00-00'],
                'Created Date'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                6,
                ['nullable' => false],
                'Status'
            )->addColumn(
                'num_account',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false, 'default' => '0'],
                'Number account'
            )->addColumn(
                'total_sales_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Total Sales Amount'
            )->addColumn(
                'commission_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Commission Type'
            )->addColumn(
                'commission',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Commission'
            )->addColumn(
                'discount_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Discount Type'
            )->addColumn(
                'discount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Discount'
            )->addColumn(
                'autojoin',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'default' => 0],
                'Auto Join config'
            )->addColumn(
                'scope',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'default' => 0],
                'Scope: All Affiliates, Customer Groups, Assigned Affiliates'
            )->addColumn(
                'customer_groups',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => ''],
                'Scope: All Affiliates, Customer Groups, Assigned Affiliates'
            )->addColumn(
                'show_in_welcome',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'default' => 0],
                'Show program in welcome page'
            )->addColumn(
                'valid_from',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                255,
                ['default' => '0000-00-00'],
                'Valid From Date'
            )->addColumn(
                'valid_to',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                255,
                ['default' => '0000-00-00'],
                'Valid To Date'
            )->addColumn(
                'use_coupon',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'default' => '0'],
                'Use Coupon Config'
            )->addColumn(
                'coupon_pattern',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => ''],
                'Coupon Pattern'
            )->addColumn(
                'affiliate_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => ''],
                'Affiliate Type'
            )->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => ''],
                'Description'
            )->addColumn(
                'conditions_serialized',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => ''],
                'Conditions Serialized'
            )->addColumn(
                'actions_serialized',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => ''],
                'Actions Serialized'
            )->addColumn(
                'is_process',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'default' => '0'],
                'Is Process'
            )->addColumn(
                'use_tier_config',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'default' => '1'],
                'Use Tier Config'
            )->addColumn(
                'max_level',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'default' => '0'],
                'Max Level'
            )->addColumn(
                'tier_commission',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => ''],
                'Tier Commission'
            )->addColumn(
                'sec_commission',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'default' => '0'],
                'Secondary Commission'
            )->addColumn(
                'sec_commission_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['nullable' => true, 'default' => ''],
                'Secondary Commission Type'
            )->addColumn(
                'secondary_commission',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000'],
                'Secondary Commission Value'
            )->addColumn(
                'sec_discount',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'default' => '0'],
                'Secondary Discount'
            )->addColumn(
                'sec_discount_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['nullable' => true, 'default' => ''],
                'Secondary Discount Type'
            )->addColumn(
                'secondary_discount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                [12, 4],
                ['nullable' => false, 'default' => '0.0000'],
                'Secondary Discount Value'
            )->addColumn(
                'customer_group_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => ''],
                'Customer Group Ids'
            )->addColumn(
                'use_sec_tier',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'default' => '1'],
                'Use Secondary Commission to Tier Affiliate'
            )->addColumn(
                'sec_tier_commission',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => true, 'default' => ''],
                'Secondary Commission Value to Tier Affiliate'
            )->addColumn(
                'priority',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['default' => 0],
                'Program Priority'
            )
            ->addIndex(
                $installer->getIdxName(self::SCHEMA_PROGRAM_PROGRAM, ['program_id']),
                ['program_id']
            )
            ->setComment('Program Table');
        $installer->getConnection()->createTable($table);
        /**
         * End create table 'magestore_affiliateplusprogram'
         */

        /**
         * Create table 'magestore_affiliateplusprogram_value'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_PROGRAM_VALUE))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Program Value Id'
            )->addColumn(
                'program_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Program Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                5,
                ['nullable' => false, 'default' => '0'],
                'Store Id'
            )->addColumn(
                'attribute_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Address Html'
            )->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Value'
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PROGRAM_VALUE, ['program_id']),
                ['program_id']
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PROGRAM_VALUE, ['store_id']),
                ['store_id']
            )->addIndex(
                $installer->getIdxName(
                    self::SCHEMA_PROGRAM_VALUE,
                    ['program_id', 'store_id', 'attribute_code'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['program_id', 'store_id', 'attribute_code'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PROGRAM_VALUE, 'program_id', self::SCHEMA_PROGRAM_PROGRAM, 'program_id'),
                'program_id',
                $installer->getTable(self::SCHEMA_PROGRAM_PROGRAM),
                'program_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Program value table');
        $installer->getConnection()->createTable($table);

        /**
         * End create table 'magestore_affiliateplusprogram_value'
         */

        /**
         * Create table 'magestore_affiliateplusprogram_category'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_PROGRAM_CATEGORY))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )->addColumn(
                'program_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Program Id'
            )->addColumn(
                'category_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Category Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                5,
                ['unsigned' => true,'nullable' => false],
                'Store Id'
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PROGRAM_CATEGORY, ['program_id']),
                ['program_id']
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PROGRAM_CATEGORY, ['category_id']),
                ['category_id']
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PROGRAM_CATEGORY, ['store_id']),
                ['store_id']
            )->addIndex(
                $installer->getIdxName(
                    self::SCHEMA_PROGRAM_CATEGORY,
                    ['program_id', 'category_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['program_id', 'category_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PROGRAM_CATEGORY, 'program_id', self::SCHEMA_PROGRAM_PROGRAM, 'program_id'),
                'program_id',
                $installer->getTable(self::SCHEMA_PROGRAM_PROGRAM),
                'program_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PROGRAM_CATEGORY, 'category_id', self::SCHEMA_CATALOG_CATEGORY, 'entity_id'),
                'category_id',
                $installer->getTable(self::SCHEMA_CATALOG_CATEGORY),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PROGRAM_CATEGORY, 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Program Category Table');
        $installer->getConnection()->createTable($table);
        /**
         * End create table 'magestore_affiliateplusprogram_category'
         */

        /**
         * Create table 'magestore_affiliateplusprogram_product'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_PROGRAM_PRODUCT))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )->addColumn(
                'program_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Program Id'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Product Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                5,
                ['unsigned' => true,'nullable' => false],
                'Store Id'
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PROGRAM_PRODUCT, ['program_id']),
                ['program_id']
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PROGRAM_PRODUCT, ['product_id']),
                ['product_id']
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PROGRAM_PRODUCT, ['store_id']),
                ['store_id']
            )->addIndex(
                $installer->getIdxName(
                    self::SCHEMA_PROGRAM_PRODUCT,
                    ['program_id', 'product_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['program_id', 'product_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PROGRAM_PRODUCT, 'program_id', self::SCHEMA_PROGRAM_PROGRAM, 'program_id'),
                'program_id',
                $installer->getTable(self::SCHEMA_PROGRAM_PROGRAM),
                'program_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PROGRAM_PRODUCT, 'product_id', self::SCHEMA_CATALOG_PRODUCT, 'entity_id'),
                'product_id',
                $installer->getTable(self::SCHEMA_CATALOG_PRODUCT),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PROGRAM_PRODUCT, 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Program Product Table');
        $installer->getConnection()->createTable($table);
        /**
         * End create table 'magestore_affiliateplusprogram_product'
         */

        /**
         * Create table 'magestore_affiliateplusprogram_account'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_PROGRAM_ACCOUNT))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )->addColumn(
                'program_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Program Id'
            )->addColumn(
                'account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Affiliate Program Id'
            )->addColumn(
                'joined',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                255,
                ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                'Joined date'
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PROGRAM_ACCOUNT, ['program_id']),
                ['program_id']
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PROGRAM_ACCOUNT, ['account_id']),
                ['account_id']
            )->addIndex(
                $installer->getIdxName(
                    self::SCHEMA_PROGRAM_PRODUCT,
                    ['program_id', 'account_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['program_id', 'account_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PROGRAM_ACCOUNT, 'program_id', self::SCHEMA_PROGRAM_PROGRAM, 'program_id'),
                'program_id',
                $installer->getTable(self::SCHEMA_PROGRAM_PROGRAM),
                'program_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PROGRAM_ACCOUNT, 'account_id', self::SCHEMA_ACCOUNT, 'account_id'),
                'account_id',
                $installer->getTable(self::SCHEMA_ACCOUNT),
                'account_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Program Program Table');
        $installer->getConnection()->createTable($table);
        /**
         * End create table 'magestore_affiliateplusprogram_account'
         */

        /**
         * Create table 'magestore_affiliateplusprogram_transaction'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_PROGRAM_TRANSACTION))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )->addColumn(
                'transaction_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Transaction Id'
            )->addColumn(
                'program_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Program Id'
            )->addColumn(
                'account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Affiliate Program Id'
            )->addColumn(
                'program_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Program Name'
            )->addColumn(
                'account_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Affiliate Program Name'
            )->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Order Id'
            )->addColumn(
                'order_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => ''],
                'Order Number'
            )->addColumn(
                'order_item_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => ''],
                'Order Item Id'
            )->addColumn(
                'order_item_names',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => ''],
                'Order Item Name'
            )->addColumn(
                'total_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                [12,4],
                ['nullable' => false, 'default' => '0.0000'],
                'Total Amount'
            )->addColumn(
                'commission',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                [12,4],
                ['nullable' => false, 'default' => '0.0000'],
                'Commission'
            )->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'default' => '3'],
                'Transaction type'
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PROGRAM_TRANSACTION, ['transaction_id']),
                ['transaction_id']
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PROGRAM_TRANSACTION, ['account_id']),
                ['account_id']
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PROGRAM_TRANSACTION, ['program_id']),
                ['program_id']
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PROGRAM_TRANSACTION, 'program_id', self::SCHEMA_PROGRAM_PROGRAM, 'program_id'),
                'program_id',
                $installer->getTable(self::SCHEMA_PROGRAM_PROGRAM),
                'program_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PROGRAM_TRANSACTION, 'account_id', self::SCHEMA_ACCOUNT, 'account_id'),
                'account_id',
                $installer->getTable(self::SCHEMA_ACCOUNT),
                'account_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PROGRAM_TRANSACTION, 'transaction_id', self::SCHEMA_TRANSACTION, 'transaction_id'),
                'transaction_id',
                $installer->getTable(self::SCHEMA_TRANSACTION),
                'transaction_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Program Transaction Table');
        $installer->getConnection()->createTable($table);
        /**
         * End create table 'magestore_affiliateplusprogram_account'
         */

        /**
         * Create table 'magestore_affiliateplusprogram_joined'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_PROGRAM_JOINED))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )->addColumn(
                'program_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Program Id'
            )->addColumn(
                'account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Affiliate Program Id'
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PROGRAM_JOINED, ['program_id']),
                ['program_id']
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PROGRAM_JOINED, ['account_id']),
                ['account_id']
            )->addIndex(
                $installer->getIdxName(
                    self::SCHEMA_PROGRAM_PRODUCT,
                    ['program_id', 'account_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['program_id', 'account_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PROGRAM_JOINED, 'program_id', self::SCHEMA_PROGRAM_PROGRAM, 'program_id'),
                'program_id',
                $installer->getTable(self::SCHEMA_PROGRAM_PROGRAM),
                'program_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PROGRAM_JOINED, 'account_id', self::SCHEMA_ACCOUNT, 'account_id'),
                'account_id',
                $installer->getTable(self::SCHEMA_ACCOUNT),
                'account_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Program Joined');
        $installer->getConnection()->createTable($table);
        /**
         * End create table 'magestore_affiliateplusprogram_joined'
         */

        /**
         * Add field "program_id" into magestore_affiliateplus_banner table
         */
        $installer->getConnection()->addColumn(
            $installer->getTable(self::SCHEMA_BANNER),
            'program_id',
            'INT(10)'
        );

        $installer->endSetup();
    }

}
