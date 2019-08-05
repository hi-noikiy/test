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
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Affiliateplus\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var \Magento\Sales\Setup\SalesSetupFactory
     */
    protected $_salesSetupFactory;
    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_pageModel;
    /**
     * schema table
     */
    const SCHEMA_ACCOUNT =       'magestore_affiliateplus_account';
    const SCHEMA_ACCOUNT_VALUE=  'magestore_affiliateplus_account_value';
    const SCHEMA_BANNER =        'magestore_affiliateplus_banner';
    const SCHEMA_BANNER_VALUE =  'magestore_affiliateplus_banner_value';
    const SCHEMA_TRANSACTION =   'magestore_affiliateplus_transaction';
    const SCHEMA_PAYMENT =       'magestore_affiliateplus_payment';
    const SCHEMA_PAYMENT_PAYPAL ='magestore_affiliateplus_payment_paypal';
    const SCHEMA_REFERER =       'magestore_affiliateplus_referer';
    const SCHEMA_ACTION =        'magestore_affiliateplus_action';
    const SCHEMA_PAYMENT_VERIFY= 'magestore_affiliateplus_payment_verify';
    const SCHEMA_TRACKING =      'magestore_affiliateplus_tracking';
    const SCHEMA_CREDIT =        'magestore_affiliateplus_credit';
    const SCHEMA_PAYMENY_HISTORY='magestore_affiliateplus_payment_history';
    const SCHEMA_PAYMENY_OFFLINE='magestore_affiliatepluspayment_offline';
    const SCHEMA_PAYMENY_BANK   ='magestore_affiliatepluspayment_bank';
    const SCHEMA_PAYMENY_BANKACCOUNT   ='magestore_affiliatepluspayment_bankaccount';
    const SCHEMA_PAYMENY_MONERBOOKER   ='magestore_affiliatepluspayment_moneybooker';


    /**
     * InstallSchema constructor.
     * @param \Magento\Cms\Model\PageFactory $pageModel
     * @param \Magento\Sales\Setup\SalesSetup $salesSetupFactory
     */
    public function __construct(
        \Magento\Cms\Model\PageFactory $pageModel,
        \Magento\Sales\Setup\SalesSetup $salesSetupFactory
    )
    {
        $this->_pageModel = $pageModel;
        $this->_salesSetupFactory = $salesSetupFactory;
    }
    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_ACCOUNT));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_ACCOUNT_VALUE));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_BANNER));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_BANNER_VALUE));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_TRANSACTION));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_PAYMENT));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_REFERER));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_ACTION));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_PAYMENT_VERIFY));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_TRACKING));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_CREDIT));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_PAYMENY_HISTORY));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_PAYMENY_OFFLINE));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_PAYMENY_BANK));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_PAYMENY_BANKACCOUNT));
        $installer->getConnection()->dropTable($installer->getTable(self::SCHEMA_PAYMENY_MONERBOOKER));


        /**
         * Create table 'magestore_affiliatepluspayment_moneybooker'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_PAYMENY_MONERBOOKER))
            ->addColumn(
                'payment_moneybooker_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Payment Moneybooker Id'
            )->addColumn(
                'payment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Payment Id'
            )->addColumn(
                'email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Email'
            )->addColumn(
                'transaction_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Transaction Id'
            )->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Description'
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PAYMENY_MONERBOOKER, ['payment_id']),
                ['payment_id']
            )->addIndex(
                $installer->getIdxName(
                    self::SCHEMA_PAYMENY_MONERBOOKER,
                    ['payment_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['payment_id',],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PAYMENY_MONERBOOKER, 'payment_id', self::SCHEMA_PAYMENT, 'payment_id'),
                'payment_id',
                $installer->getTable(self::SCHEMA_PAYMENT),
                'payment_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Payment Offline');
        $installer->getConnection()->createTable($table);

        /**
         * End create table 'magestore_affiliatepluspayment_moneybooker'
         */

        /**
         * Create table 'magestore_affiliatepluspayment_offline'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_PAYMENY_OFFLINE))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )->addColumn(
                'payment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Payment Id'
            )->addColumn(
                'address_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['nullable' => false, 'default' => '0'],
                'Address Id'
            )->addColumn(
                'address_html',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Address Html'
            )->addColumn(
                'transfer_info',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Transfer Info'
            )->addColumn(
                'message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Message'
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PAYMENY_OFFLINE, ['payment_id']),
                ['payment_id']
            )->addIndex(
                $installer->getIdxName(
                    self::SCHEMA_PAYMENY_OFFLINE,
                    ['payment_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['payment_id',],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PAYMENY_OFFLINE, 'payment_id', self::SCHEMA_PAYMENT, 'payment_id'),
                'payment_id',
                $installer->getTable(self::SCHEMA_PAYMENT),
                'payment_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Payment Offline');
        $installer->getConnection()->createTable($table);

        /**
         * End create table 'magestore_affiliatepluspayment_offline'
         */

        /**
         * Create table 'magestore_affiliatepluspayment_bank'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_PAYMENY_BANK))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )->addColumn(
                'payment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Payment Id'
            )->addColumn(
                'bankaccount_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Bankaccount Id'
            )->addColumn(
                'bankaccount_html',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Bankaccount Html'
            )->addColumn(
                'invoice_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Invoice Number'
            )->addColumn(
                'message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default' => ''],
                'Message'
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PAYMENY_BANK, ['payment_id']),
                ['payment_id']
            )->addIndex(
                $installer->getIdxName(
                    self::SCHEMA_PAYMENY_BANK,
                    ['payment_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['payment_id',],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PAYMENY_BANK, 'payment_id', self::SCHEMA_PAYMENT, 'payment_id'),
                'payment_id',
                $installer->getTable(self::SCHEMA_PAYMENT),
                'payment_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Payment Bank');
        $installer->getConnection()->createTable($table);

        /**
         * End create table 'magestore_affiliatepluspayment_bank'
         */

        /**
         * Create table 'magestore_affiliatepluspayment_bankaccount'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_PAYMENY_BANKACCOUNT))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )->addColumn(
                'account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Account Id'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Name'
            )->addColumn(
                'address',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Address'
            )->addColumn(
                'account_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Account Name'
            )->addColumn(
                'account_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Account Number'
            )->addColumn(
                'routing_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Routing Code'
            )->addColumn(
                'swift_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default' => ''],
                'Routing Code'
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PAYMENY_BANKACCOUNT, ['account_id']),
                ['account_id']
            )->addIndex(
                $installer->getIdxName(
                    self::SCHEMA_PAYMENY_BANKACCOUNT,
                    ['account_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['account_id',],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PAYMENY_BANKACCOUNT, 'account_id', self::SCHEMA_ACCOUNT, 'account_id'),
                'account_id',
                $installer->getTable(self::SCHEMA_ACCOUNT),
                'account_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Payment Offline');
        $installer->getConnection()->createTable($table);

        /**
         * End create table 'magestore_affiliatepluspayment_bankaccount'
         */

        /**
         * Create table 'magestore_affiliateplus_account'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_ACCOUNT))
            ->addColumn(
                'account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Account Id'
            )->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Customer Id'
            )->addColumn(
                'address_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false, 'default' => '0'],
                'Quote Id'
            )->addColumn(
                'identify_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                63,
                ['nullable' => false, 'default' => ''],
                'Identify Code'
            )->addColumn(
                'name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Name'
            )->addColumn(
                'email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Email'
            )->addColumn(
                'balance',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Balance'
            )->addColumn(
                'total_commission_received',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Total Commission Received'
            )->addColumn(
                'total_paid',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Total Paid'
            )->addColumn(
                'total_clicks',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false, 'default' => '0'],
                'Total Clicks'
            )->addColumn(
                'unique_clicks',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false, 'default' => '0'],
                'Unique Clicks'
            )->addColumn(
                'paypal_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Paypal Email'
            )->addColumn(
                'created_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                255,
                ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                'Created Time'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                6,
                ['nullable' => false],
                'status'
            )->addColumn(
                'approved',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                6,
                ['nullable' => false, 'default' => '2'],
                'Approved'
            )->addColumn(
                'notification',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                6,
                ['nullable' => false, 'default' => '1'],
                'Notification'
            )->addColumn(
                'referring_website',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => null],
                'Referring Website'
            )->addColumn(
                'recurring_payment',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'default' => '1'],
                'Recurring Payment'
            )->addColumn(
                'last_received_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['nullable' => true],
                'Last Received Date'
            )->addColumn(
                'recurring_method',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => true, 'default' => 'paypal'],
                'Recurring Method'
            )->addColumn(
                'moneybooker_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => true, 'default' => ''],
                'Moneybooker Email'
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_ACCOUNT, ['customer_id']),
                ['customer_id']
            )->addIndex(
                $installer->getIdxName(
                    self::SCHEMA_ACCOUNT,
                    ['customer_id', 'identify_code'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['customer_id', 'identify_code'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_ACCOUNT, 'customer_id', 'customer_entity', 'entity_id'),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Account account');
        $installer->getConnection()->createTable($table);

        /**
         * End create table 'magestore_affiliatepluspayment_offline'
         */

        /**
         * Create table 'magestore_affiliateplus_account_value'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_ACCOUNT_VALUE))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Value Id'
            )->addColumn(
                'account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Customer Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                5,
                ['unsigned' => true,'nullable' => false],
                'Store Id'
            )->addColumn(
                'attribute_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                63,
                ['nullable' => false, 'default' => ''],
                'Attribute Code'
            )->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
               null,
                ['nullable' => false],
                'Value'
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_ACCOUNT_VALUE, ['account_id']),
                ['account_id']
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_ACCOUNT_VALUE, ['store_id']),
                ['store_id']
            )->addIndex(
                $installer->getIdxName(
                    self::SCHEMA_ACCOUNT_VALUE,
                    ['account_id', 'store_id','attribute_code'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['account_id', 'store_id','attribute_code'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_ACCOUNT_VALUE, 'account_id', self::SCHEMA_ACCOUNT, 'account_id'),
                'account_id',
                $installer->getTable(self::SCHEMA_ACCOUNT),
                'account_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Account account value');
        $installer->getConnection()->createTable($table);
        /**
         * End create table 'magestore_affiliateplus_account_value'
         */
        /**
         * Create table 'magestore_affiliateplus_banner'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_BANNER))
            ->addColumn(
                'banner_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Banner Id'
            )->addColumn(
                'title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => '0'],
                'Title'
            )->addColumn(
                'type_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                5,
                ['nullable' => false, 'default' => '1'],
                'Type Id'
            )->addColumn(
                'source_file',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Source File'
            )->addColumn(
                'width',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Width'
            )->addColumn(
                'height',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false],
                'Height'
            )->addColumn(
                'link',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Link'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                6,
                ['nullable' => false, 'default' => '1'],
                'Status'
            )->setComment('Account banner');
        $installer->getConnection()->createTable($table);
        /**
         * End create table 'magestore_affiliateplus_banner'
         */
        /**
         * Create table 'magestore_affiliateplus_banner_value'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_BANNER_VALUE))
            ->addColumn(
                'value_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Value Id'
            )->addColumn(
                'banner_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Banner Id'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                5,
                ['unsigned' => true,'nullable' => false],
                'Store Id'
            )->addColumn(
                'attribute_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                63,
                ['nullable' => false, 'default' => ''],
                'Attribute Code'
            )->addColumn(
                'value',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Value'
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_BANNER_VALUE, ['banner_id']),
                ['banner_id']
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_BANNER_VALUE, ['store_id']),
                ['store_id']
            )->addIndex(
                $installer->getIdxName(
                    self::SCHEMA_ACCOUNT_VALUE,
                    ['banner_id', 'store_id','attribute_code'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['banner_id', 'store_id','attribute_code'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_BANNER_VALUE, 'banner_id', self::SCHEMA_BANNER, 'banner_id'),
                'banner_id',
                $installer->getTable(self::SCHEMA_BANNER),
                'banner_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_BANNER_VALUE, 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Account banner value');
        $installer->getConnection()->createTable($table);
        /**
         * End create table 'magestore_affiliateplus_banner_value'
         */
        /**
         * Create table 'magestore_affiliateplus_transaction'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_TRANSACTION))
            ->addColumn(
                'transaction_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Transaction Id'
            )->addColumn(
                'account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Account Id'
            )->addColumn(
                'account_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
               255,
                ['nullable' => false, 'default' => ''],
                'Account Name'
            )->addColumn(
                'account_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Account Email'
            )->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Customer Id'
            )->addColumn(
                'customer_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Customer Email'
            )->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Order Id'
            )->addColumn(
                'order_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['default' => ''],
                'Order Number'
            )->addColumn(
                'order_item_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => ''],
                'Order Item Ids'
            )->addColumn(
                'order_item_names',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => ''],
                'Order Item Names'
            )->addColumn(
                'total_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Total Amount'
            )->addColumn(
                'commission',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Commission'
            )->addColumn(
                'discount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Discount'
            )->addColumn(
                'created_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                255,
                ['nullable' => true],
                'Created Time'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                6,
                ['nullable' => false, 'default' => '1'],
                'Status'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [ 'unsigned' => true, 'nullable' => false],
                'Store Id'
            )->addColumn(
                'percent_plus',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Percent Plus'
            )->addColumn(
                'commission_plus',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Commission Plus'
            )->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                2,
                ['nullable' => false, 'default' => '3'],
                'Type'
            )->addColumn(
                'banner_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['nullable' => true],
                'Banner Id'
            )->addColumn(
                'creditmemo_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default'=> ''],
                'Creditmemo Ids'
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_TRANSACTION, ['account_id']),
                ['account_id']
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_TRANSACTION, ['store_id']),
                ['store_id']
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_TRANSACTION, 'account_id', self::SCHEMA_ACCOUNT, 'account_id'),
                'account_id',
                $installer->getTable(self::SCHEMA_ACCOUNT),
                'account_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(self::SCHEMA_TRANSACTION, 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Account transaction');
        $installer->getConnection()->createTable($table);
        /**
         * End create table 'magestore_affiliate_transaction'
         */

        /**
         * Create table 'magestore_affiliateplus_payment'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_PAYMENT))
            ->addColumn(
                'payment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Payment Id'
            )->addColumn(
                'account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Account Id'
            )->addColumn(
                'account_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Account Name'
            )->addColumn(
                'account_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Account Email'
            )->addColumn(
                'payment_method',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                63,
                ['nullable' => false, 'default'=> ''],
                'Payment Method'
            )->addColumn(
                'amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Amount'
            )->addColumn(
                'fee',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Fee'
            )->addColumn(
                'tax_amount',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Tax Amount'
            )->addColumn(
                'amount_incl_tax',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => '0.0000'],
                'Amount Incl Tax'
            )->addColumn(
                'request_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Request Time'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                3,
                ['nullable' => false, 'default' => '1'],
                'Status'
            )->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Description'
            )->addColumn(
                'store_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Store Ids'
            )->addColumn(
                'is_request',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                3,
                ['nullable' => false, 'default' => '1'],
                'Is Request'
            )->addColumn(
                'is_payer_fee',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                3,
                ['nullable' => false, 'default' => '1'],
                'Is Payer Fee'
            )->addColumn(
                'is_reduced_balance',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                3,
                ['nullable' => false, 'default' => '0'],
                'Is Reduced Balance'
            )->addColumn(
                'is_refund_balance',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                3,
                ['nullable' => false, 'default' => '0'],
                'Is Refund Balance'
            )->addColumn(
                'is_recurring',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['nullable' => false, 'default' => '0'],
                'Is Recurring'
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PAYMENT, ['account_id']),
                ['account_id']
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PAYMENT, 'account_id', self::SCHEMA_ACCOUNT, 'account_id'),
                'account_id',
                $installer->getTable(self::SCHEMA_ACCOUNT),
                'account_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Account payment');
        $installer->getConnection()->createTable($table);
        /**
         * End create table 'magestore_affiliateplus_payment'
         */


        /**
         * Create table 'magestore_affiliateplus_payment_paypal'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_PAYMENT_PAYPAL))
            ->addColumn(
                'payment_paypal_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Payment Paypal Id'
            )->addColumn(
                'payment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Payment Id'
            )->addColumn(
                'email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Email'
            )->addColumn(
                'transaction_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default'=> ''],
                'Transaction Id'
            )->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default'=> ''],
                'Description'
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PAYMENT_PAYPAL, ['payment_id']),
                ['payment_id']
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PAYMENT_PAYPAL, 'payment_id', self::SCHEMA_PAYMENT, 'payment_id'),
                'payment_id',
                $installer->getTable(self::SCHEMA_PAYMENT),
                'payment_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Account payment paypal');
        $installer->getConnection()->createTable($table);
        /**
         * End create table 'magestore_affiliateplus_payment_paypal'
         */


        /**
         * Create table 'magestore_affiliateplus_referer'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_REFERER))
            ->addColumn(
                'referer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Referer Id'
            )->addColumn(
                'account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Account Id'
            )->addColumn(
                'referer',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Referer'
            )->addColumn(
                'url_path',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default'=> ''],
                'Url Path'
            )->addColumn(
                'total_clicks',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false, 'default'=> '0'],
                'Total Clicks'
            )->addColumn(
                'unique_clicks',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => false, 'default'=> '0'],
                'Unique Clicks'
            )->addColumn(
                'ip_list',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default'=> ''],
                'Ip List'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                [ 'unsigned' => true, 'nullable' => false],
                'Store Id'
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_REFERER, ['account_id']),
                ['account_id']
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_REFERER, ['store_id']),
                ['store_id']
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_REFERER, 'account_id', self::SCHEMA_ACCOUNT, 'account_id'),
                'account_id',
                $installer->getTable(self::SCHEMA_ACCOUNT),
                'account_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(self::SCHEMA_REFERER, 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Account payment paypal');
        $installer->getConnection()->createTable($table);
        /**
         * End create table 'magestore_affiliateplus_referer'
         */


        /**
         * Create table 'magestore_affiliateplus_action'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_ACTION))
            ->addColumn(
                'action_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                19,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Action Id'
            )->addColumn(
                'account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Account Id'
            )->addColumn(
                'account_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default' => ''],
                'Account Email'
            )->addColumn(
                'banner_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['nullable' => false, 'default'=> '0'],
                'Banner Id'
            )->addColumn(
                'banner_title',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default'=> ''],
                'Banner Title'
            )->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                3,
                ['nullable' => false, 'default'=> '2'],
                'Type'
            )->addColumn(
                'ip_address',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false, 'default'=> ''],
                'Ip Quote'
            )->addColumn(
                'is_unique',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                3,
                ['nullable' => false, 'default'=> '0'],
                'Is Unique'
            )->addColumn(
                'is_commission',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                5,
                ['nullable' => false, 'default'=> '0'],
                'Is Commission'
            )->addColumn(
                'domain',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default'=> ''],
                'Domain'
            )->addColumn(
                'referer',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default'=> ''],
                'Referer'
            )->addColumn(
                'landing_page',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default'=> ''],
                'Landing Page'
            )->addColumn(
                'totals',
                \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT,
                8,
                ['nullable' => false, 'default'=> '0'],
                'Totals'
            )->addColumn(
                'created_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['nullable' => null],
                'Created Date'
            )->addColumn(
                'updated_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => null],
                'Updated Time'
            )->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                5,
                ['unsigned' => true,'nullable' => null],
                'Store Id'
            )->addColumn(
                'direct_link',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                5,
                ['unsigned' => true,'nullable' => null],
                'Direct Link'
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_ACTION, ['account_id']),
                ['account_id']
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_ACTION, ['store_id']),
                ['store_id']
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_ACTION, 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Account action');
        $installer->getConnection()->createTable($table);
        /**
         * End create table 'magestore_affiliateplus_action'
         */

        /**
         * Create table 'magestore_affiliateplus_payment_verify'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable(self::SCHEMA_PAYMENT_VERIFY))
            ->addColumn(
                'verify_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Verify Id'
            )->addColumn(
                'account_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                10,
                ['unsigned' => true,'nullable' => false],
                'Account Id'
            )->addColumn(
                'payment_method',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                63,
                ['nullable' => false, 'default' => ''],
                'Payment Method'
            )->addColumn(
                'field',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                100,
                ['nullable' => false, 'default'=> ''],
                'Field'
            )->addColumn(
                'info',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default'=> ''],
                'Info'
            )->addColumn(
                'verified',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                3,
                ['nullable' => false, 'default'=> '2'],
                'Verified'
            )->addIndex(
                $installer->getIdxName(self::SCHEMA_PAYMENT_VERIFY, ['account_id']),
                ['account_id']
            )->addForeignKey(
                $installer->getFkName(self::SCHEMA_PAYMENT_VERIFY, 'account_id', self::SCHEMA_ACCOUNT, 'account_id'),
                'account_id',
                $installer->getTable(self::SCHEMA_ACCOUNT),
                'account_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->setComment('Account payment verify');
        $installer->getConnection()->createTable($table);
        /**
         * End create table 'magestore_affiliateplus_payment_verify'
         */

		/**
		 * Create table 'magestore_affiliateplus_tracking'
		 */
		$table = $installer->getConnection()
			->newTable($installer->getTable(self::SCHEMA_TRACKING))
			->addColumn(
				'tracking_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				10,
				['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
				'Tracking Id'
			)->addColumn(
			'account_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			10,
			['unsigned' => true, 'nullable' => false],
			'Account Id'
		)->addColumn(
			'customer_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			10,
			['nullable' => false],
			'Customer Id'
		)->addColumn(
			'customer_email',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => false, 'default' => ''],
			'Customer Email'
		)->addColumn(
			'created_time',
			\Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
			null,
			['nullable' => true],
			'Created Time'
		)->addIndex(
			$installer->getIdxName(self::SCHEMA_PAYMENT_VERIFY, ['account_id']),
			['account_id']
		)->addForeignKey(
			$installer->getFkName(self::SCHEMA_TRACKING, 'account_id', self::SCHEMA_ACCOUNT, 'account_id'),
			'account_id',
			$installer->getTable(self::SCHEMA_ACCOUNT),
			'account_id',
			\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
		)->setComment('Account tracking');
		$installer->getConnection()->createTable($table);
		/**
		 * End create table 'magestore_affiliateplus_tracking'
		 */

		/**
		 *Create table 'magestore_affiliateplus_credit'
		 */
		$table = $installer->getConnection()
			->newTable($installer->getTable(self::SCHEMA_CREDIT))
			->addColumn(
				'id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				10,
				['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
				'Id'
			)->addColumn(
			'payment_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			10,
			['unsigned' => true, 'nullable' => false],
			'Payment Id'
		)->addColumn(
			'order_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			10,
			['unsigned' => true, 'nullable' => false],
			'Order Id'
		)->addColumn(
			'order_increment_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => false, 'default' => ''],
			'Order Increment Id'
		)->addColumn(
			'base_paid_amount',
			\Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
			'12,4',
			['nullable' => true, 'default' => '0.0000'],
			'Base Paid Amount'
		)->addColumn(
			'paid_amount',
			\Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
			'12,4',
			['nullable' => true, 'default' => '0.0000'],
			'Paid Amount'
		)->addColumn(
			'base_refund_amount',
			\Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
			'12,4',
			['nullable' => true, 'default' => '0.0000'],
			'Base Refund Amount'
		)->addColumn(
			'refund_amount',
			\Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
			'12,4',
			['nullable' => true, 'default' => '0.0000'],
			'Refund Amount'
		)->addIndex(
			$installer->getIdxName(self::SCHEMA_CREDIT, ['payment_id']),
			['payment_id']
		)->addForeignKey(
			$installer->getFkName(self::SCHEMA_CREDIT, 'payment_id', self::SCHEMA_PAYMENT, 'payment_id'),
			'payment_id',
			$installer->getTable(self::SCHEMA_PAYMENT),
			'payment_id',
			\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
		)->setComment('Account credit');
		$installer->getConnection()->createTable($table);
		/**
		 * End create table 'magestore_affiliateplus_credit'
		 */

		/**
		 * Create table 'magestore_affiliateplus_payment_history'
		 */
		$table = $installer->getConnection()
			->newTable($installer->getTable(self::SCHEMA_PAYMENY_HISTORY))
			->addColumn(
				'history_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				10,
				['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
				'History Id'
			)->addColumn(
			'payment_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			10,
			['unsigned' => true, 'nullable' => false],
			'Payment Id'
		)->addColumn(
			'status',
			\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
			3,
			['nullable' => false, 'default' => '1'],
			'Status'
		)->addColumn(
			'created_time',
			\Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
			null,
			['nullable' => true],
			'Created Time'
		)->addColumn(
			'description',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			null,
			['nullable' => false],
			'Description'
		)->addIndex(
			$installer->getIdxName(self::SCHEMA_CREDIT, ['payment_id']),
			['payment_id']
		)->addForeignKey(
			$installer->getFkName(self::SCHEMA_PAYMENY_HISTORY, 'payment_id', self::SCHEMA_PAYMENT, 'payment_id'),
			'payment_id',
			$installer->getTable(self::SCHEMA_PAYMENT),
			'payment_id',
			\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
		)->setComment('Account payment history');
		$installer->getConnection()->createTable($table);
		/**
		 * End create table 'magestore_affiliateplus_payment_history'
		 */

		/**
		 * add column to table 'sales_flat_order'
		 */
		$tableSalesOrder = $installer->getTable('sales_order');

		$installer->getConnection()->addColumn(
			$tableSalesOrder,
				'affiliateplus_discount',
				'DECIMAL(12,4)'

		);
		$installer->getConnection()->addColumn(
			$tableSalesOrder,
			'base_affiliateplus_discount',
				'DECIMAL(12,4)'
		);
		$installer->getConnection()->addColumn(
			$tableSalesOrder,
			'base_affiliate_credit',
				'DECIMAL(12,4)'
		);
		$installer->getConnection()->addColumn(
			$tableSalesOrder,
			'affiliate_credit',
				'DECIMAL(12,4)'
		);

        $installer->getConnection()->addColumn(
			$tableSalesOrder,
			'account_ids',
            'INT(10)'
		);


        /**
         * add attribute
         */

		$tableSalesiInvoice = $installer->getTable('sales_invoice');
		$installer->getConnection()->addColumn(
			$tableSalesiInvoice,
			'affiliateplus_discount',
				'DECIMAL(12,4)'
		);
		$installer->getConnection()->addColumn(
			$tableSalesiInvoice,
			'base_affiliateplus_discount',
				'DECIMAL(12,4)'
		);
		$installer->getConnection()->addColumn(
			$tableSalesiInvoice,
			'affiliate_credit',
				'DECIMAL(12,4)'
		);
		$installer->getConnection()->addColumn(
			$tableSalesiInvoice,
			'base_affiliate_credit',
				'DECIMAL(12,4)'
		);

		$tableSalesCreditmemo = $installer->getTable('sales_creditmemo');
		$installer->getConnection()->addColumn(
			$tableSalesCreditmemo,
			'affiliateplus_discount',
				'DECIMAL(12,4)'
		);
		$installer->getConnection()->addColumn(
			$tableSalesCreditmemo,
			'base_affiliateplus_discount',
				'DECIMAL(12,4)'
		);
		$installer->getConnection()->addColumn(
			$tableSalesCreditmemo,
			'affiliate_credit',
				'DECIMAL(12,4)'
		);
		$installer->getConnection()->addColumn(
			$tableSalesCreditmemo,
			'base_affiliate_credit',
				'DECIMAL(12,4)'
		);


		/**
		 * add column
		 */
		$installer->getConnection()
			->addColumn(
				$installer->getTable('sales_order_item'),
				'affiliateplus_commission_item',
				[
					'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					'nullable' => true,
					'default' => null,
					'comment' => 'Commission',
				]
			);
		$installer->getConnection()
			->addColumn(
				$installer->getTable('sales_order_item'),
				'affiliateplus_amount',
                'DECIMAL(12,4)'
			);
		$installer->getConnection()
			->addColumn(
				$installer->getTable('sales_order_item'),
				'base_affiliateplus_amount',
                'DECIMAL(12,4)'
			);
		$installer->getConnection()
			->addColumn(
				$installer->getTable('sales_order_item'),
				'affiliateplus_commission',
                'DECIMAL(12,4)'
			)
		;
		$installer->getConnection()->addColumn(
			$installer->getTable('sales_invoice_item'),
			'affiliateplus_commission_flag',
			[
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
				2,
				'nullable' => true,
				'default' => '0',
				'comment' => 'Flag',
			]
		);
		$installer->getConnection()->addColumn(
			$installer->getTable('sales_creditmemo_item'),
			'affiliateplus_commission_flag',
			[
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
				2,
				'nullable' => true,
				'default' => '0',
				'comment' => 'Comission Flag',
			]
		);
		$installer->getConnection()->addColumn(
			$installer->getTable(self::SCHEMA_BANNER),
			'rel_nofollow',
			[
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
				6,
				'nullable' => false,
				'default' => '0',
				'comment' => 'Nofollow',
			]
		);
		$installer->getConnection()->addColumn(
			$installer->getTable(self::SCHEMA_ACCOUNT),
			'referred_by',
			[
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				255,
				'default' => '',
				'comment' => 'Referer',
			]
		);
		$installer->getConnection()->addColumn(
			$installer->getTable('sales_order_item'),
			'affiliate_credit',
				'DECIMAL(12,4)'
		);
		$installer->getConnection()->addColumn(
			$installer->getTable('sales_order_item'),
			'base_affiliate_credit',
            'DECIMAL(12,4)'
		);
		$installer->endSetup();
	}

}
