<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $this->createAffiliateAccountTable($installer);
        $this->createAffiliateTransactionTable($installer);
        $this->createAffiliateBannerTable($installer);
        $this->createAffiliateLifetimeTable($installer);
        $this->createAffiliateLinksTable($installer);
        $this->createAffiliateProgramTable($installer);
        $this->createAffiliateCouponTable($installer);

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    private function createAffiliateCouponTable($installer)
    {
        $table = $installer->getConnection()->newTable($installer->getTable('amasty_affiliate_coupon'))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )
            ->addColumn(
                'account_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )->addColumn(
                'program_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )->addColumn(
                'coupon_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )->addColumn(
                'current_profit',
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )->addIndex(
                $installer->getIdxName('amasty_affiliate_coupon', ['account_id']),
                ['entity_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_affiliate_coupon',
                    'account_id',
                    'amasty_affiliate_account',
                    'account_id'
                ),
                'account_id',
                $installer->getTable('amasty_affiliate_account'),
                'account_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'amasty_affiliate_coupon',
                    'program_id',
                    'amasty_affiliate_program',
                    'program_id'
                ),
                'program_id',
                $installer->getTable('amasty_affiliate_program'),
                'program_id',
                Table::ACTION_CASCADE
            )
        ;

        $installer->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    private function createAffiliateAccountTable($installer)
    {
        $table = $installer->getConnection()->newTable($installer->getTable('amasty_affiliate_account'))
            ->addColumn(
                'account_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )->addColumn(
                'is_affiliate_active',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 1, 'nullable' => false]
            )->addColumn(
                'accepted_terms_conditions',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0, 'nullable' => false]
            )->addColumn(
                'receive_notifications',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0, 'nullable' => false]
            )->addColumn(
                'paypal_email',
                Table::TYPE_TEXT,
                255,
                []
            )->addColumn(
                'referring_code',
                Table::TYPE_TEXT,
                255,
                []
            )->addColumn(
                'referring_website',
                Table::TYPE_TEXT,
                255,
                []
            )->addColumn(
                'balance',
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )->addColumn(
                'on_hold',
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )->addColumn(
                'commission_paid',
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )->addColumn(
                'lifetime_commission',
                Table::TYPE_DECIMAL,
                [12, 4],
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )->addColumn(
                'widget_width',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 350]
            )->addColumn(
                'widget_height',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 350]
            )->addColumn(
                'widget_title',
                Table::TYPE_TEXT,
                255,
                ['default' => 'Bestsellers']
            )->addColumn(
                'widget_products_num',
                Table::TYPE_INTEGER,
                null,
                ['default' => 6, 'unsigned' => true]
            )->addColumn(
                'widget_type',
                Table::TYPE_TEXT,
                255,
                ['default' => \Amasty\Affiliate\Model\Account::WIDGET_TYPE_NEW, 'nullable' => false]
            )->addColumn(
                'widget_show_name',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 1, 'nullable' => false]
            )->addColumn(
                'widget_show_price',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 1, 'nullable' => false]
            )->addIndex(
                $installer->getIdxName('amasty_affiliate_account', ['account_id']),
                ['account_id']
            )->addForeignKey(
                $installer->getFkName('amasty_affiliate_account', 'customer_id', 'customer_entity', 'entity_id'),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    private function createAffiliateTransactionTable($installer)
    {
        $table = $installer->getConnection()->newTable($installer->getTable('amasty_affiliate_transaction'))
            ->addColumn(
                'transaction_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )->addColumn(
                'affiliate_account_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )->addColumn(
                'program_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )->addColumn(
                'order_increment_id',
                Table::TYPE_TEXT,
                32,
                []
            )->addColumn(
                'profit',
                Table::TYPE_DECIMAL,
                [12, 4],
                ['default' => null]
            )->addColumn(
                'balance',
                Table::TYPE_DECIMAL,
                [12, 4],
                []
            )->addColumn(
                'commission',
                Table::TYPE_DECIMAL,
                [12, 4],
                []
            )->addColumn(
                'discount',
                Table::TYPE_DECIMAL,
                [12, 4],
                []
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT]
            )->addColumn(
                'type',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'status',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addIndex(
                $installer->getIdxName('amasty_affiliate_transaction', ['transaction_id']),
                ['transaction_id']
            )->addForeignKey(
                $installer->getFkName(
                    'amasty_affiliate_transaction',
                    'affiliate_account_id',
                    'amasty_affiliate_account',
                    'account_id'
                ),
                'affiliate_account_id',
                $installer->getTable('amasty_affiliate_account'),
                'account_id',
                Table::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    private function createAffiliateBannerTable($installer)
    {
        $table = $installer->getConnection()->newTable($installer->getTable('amasty_affiliate_banner'))
            ->addColumn(
                'banner_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )->addColumn(
                'title',
                Table::TYPE_TEXT,
                null,
                []
            )->addColumn(
                'type',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'image',
                Table::TYPE_TEXT,
                255,
                []
            )->addColumn(
                'text',
                Table::TYPE_TEXT,
                null,
                []
            )->addColumn(
                'link',
                Table::TYPE_TEXT,
                255,
                []
            )->addColumn(
                'rel_no_follow',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0, 'nullable' => false]
            )->addColumn(
                'status',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0, 'nullable' => false]
            )->addIndex(
                $installer->getIdxName('amasty_affiliate_banner', ['banner_id']),
                ['banner_id']
            );

        $installer->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    private function createAffiliateLifetimeTable($installer)
    {
        $table = $installer->getConnection()->newTable($installer->getTable('amasty_affiliate_lifetime'))
            ->addColumn(
                'lifetime_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )->addColumn(
                'affiliate_account_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true]
            )->addColumn(
                'program_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )->addColumn(
                'customer_email',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addIndex(
                $installer->getIdxName('amasty_affiliate_lifetime', ['lifetime_id']),
                ['lifetime_id']
            )->addForeignKey(
                $installer->getFkName(
                    'amasty_affiliate_lifetime',
                    'lifetime_id',
                    'amasty_affiliate_account',
                    'account_id'
                ),
                'affiliate_account_id',
                $installer->getTable('amasty_affiliate_account'),
                'account_id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'amasty_affiliate_lifetime',
                    'program_id',
                    'amasty_affiliate_program',
                    'program_id'
                ),
                'program_id',
                $installer->getTable('amasty_affiliate_program'),
                'program_id',
                Table::ACTION_CASCADE
            )
        ;

        $installer->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    private function createAffiliateLinksTable($installer)
    {
        $table = $installer->getConnection()->newTable($installer->getTable('amasty_affiliate_links'))
            ->addColumn(
                'link_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )->addColumn(
                'affiliate_account_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT]
            )->addColumn(
                'link_type',
                Table::TYPE_TEXT,
                255,
                []
            )->addColumn(
                'element_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )->addIndex(
                $installer->getIdxName('amasty_affiliate_links', ['link_id']),
                ['link_id']
            )->addForeignKey(
                $installer->getFkName(
                    'amasty_affiliate_links',
                    'lifetime_id',
                    'amasty_affiliate_account',
                    'account_id'
                ),
                'affiliate_account_id',
                $installer->getTable('amasty_affiliate_account'),
                'account_id',
                Table::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    private function createAffiliateProgramTable($installer)
    {
        $table = $installer->getConnection()->newTable($installer->getTable('amasty_affiliate_program'))
            ->addColumn(
                'program_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]
            )->addColumn(
                'rule_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true]
            )->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                []
            )->addColumn(
                'withdrawal_type',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )->addColumn(
                'is_active',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0, 'nullable' => false]
            )->addColumn(
                'commission_value',
                Table::TYPE_DECIMAL,
                [12, 4],
                []
            )->addColumn(
                'commission_per_profit_amount',
                Table::TYPE_DECIMAL,
                [12, 4],
                []
            )->addColumn(
                'commission_value_type',
                Table::TYPE_TEXT,
                255,
                []
            )->addColumn(
                'from_second_order',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0, 'nullable' => false]
            )->addColumn(
                'commission_type_second',
                Table::TYPE_TEXT,
                255,
                []
            )->addColumn(
                'commission_value_second',
                Table::TYPE_DECIMAL,
                [12, 4],
                []
            )->addColumn(
                'is_lifetime',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => 0, 'nullable' => false]
            )->addColumn(
                'frequency',
                Table::TYPE_TEXT,
                255,
                []
            )->addColumn(
                'total_sales',
                Table::TYPE_DECIMAL,
                [12, 4],
                []
            )->addIndex(
                $installer->getIdxName('amasty_affiliate_program', ['program_id']),
                ['program_id']
            )->addForeignKey(
                $installer->getFkName(
                    'amasty_affiliate_program',
                    'rule_id',
                    'salesrule',
                    'rule_id'
                ),
                'rule_id',
                $installer->getTable('salesrule'),
                'rule_id',
                Table::ACTION_SET_NULL
            );

        $installer->getConnection()->createTable($table);
    }
}
