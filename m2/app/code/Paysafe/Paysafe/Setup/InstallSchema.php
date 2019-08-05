<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paysafe\Paysafe\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * install thee Paysafe schema
     * @param  SchemaSetupInterface   $setup
     * @param  ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (!$context->getVersion()) {
	        /**
	         * Prepare database for install
	         */
	        $setup->startSetup();

	        /**
	         * Create table 'paysafe_payment_information'
	         */
	        $table = $setup->getConnection()->newTable(
	            $setup->getTable('paysafe_payment_information')
	        )->addColumn(
	            'information_id',
	            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	            null,
	            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
	            'Information Id'
	        )->addColumn(
	            'customer_id',
	            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
	            null,
	            ['unsigned' => true, 'nullable' => false],
	            'Customer Id'
	        )->addColumn(
	            'environment',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            4,
	            ['nullable' => false],
	            'Environment'
	        )->addColumn(
	            'profile_id',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            100,
	            ['nullable' => false],
	            'Profile Id'
	        )->addColumn(
	            'merchant_customer_id',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            100,
	            ['nullable' => false],
	            'Merchant Customer Id'
	        )->addColumn(
	            'card_id',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            100,
	            ['nullable' => false],
	            'Card Id'
	        )->addColumn(
	            'brand',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            30,
	            ['nullable' => false],
	            'Brand'
	        )->addColumn(
	            'holder',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            100,
	            ['nullable' => true],
	            'Holder'
	        )->addColumn(
	            'email',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            60,
	            ['nullable' => false],
	            'Email'
	        )->addColumn(
	            'last_digits',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            4,
	            ['nullable' => false],
	            'Last Digits'
	        )->addColumn(
	            'expiry_month',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            2,
	            ['nullable' => false],
	            'Expiry Month'
	        )->addColumn(
	            'expiry_year',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            4,
	            ['nullable' => false],
	            'Expiry Year'
	         )->addColumn(
	            'payment_token',
	            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
	            60,
	            ['nullable' => false],
	            'Payment Token'
	        )->addColumn(
	            'payment_default',
	            \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
	            null,
	            ['nullable' => false, 'default' => '0'],
	            'Payment Default'
	        );
	        $setup->getConnection()->createTable($table);

	        /**
	         * Prepare database after install
	         */
	        $setup->endSetup();
	    }
    }
}
