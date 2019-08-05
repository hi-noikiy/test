<?php

namespace ClassyLlama\LlamaCoin\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface {

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();

        // Get tutorial_simplenews table
        $tableName = $installer->getTable('merchant_customer');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            // Create tutorial_simplenews table
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn('merchant_customer_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                        ], 'Merchant Customer ID'
                )
                ->addColumn(
                        'customer_id', Table::TYPE_INTEGER, null,
                        ['nullable' => false, 'unsigned' => true, 'primary' => true], 'Customer ID'
                )
                ->addColumn(
                        'generated_merchant_id', Table::TYPE_TEXT, null, 
                        ['nullable' => false, 'default' => ''], 'Generated merchant id'
                )
                ->setComment('Merchant Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }

        $tablename2 = $installer->getTable('optimal_creditcard');

        if ($installer->getConnection()->isTableExists($tablename2) != true) {
            // Create tutorial_simplenews table
            $table2 = $installer->getConnection()
                ->newTable($tablename2)
                ->addColumn('entity_id', Table::TYPE_INTEGER, null, [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                        ], 'Entity ID'
                )
                ->addColumn(
                    'customer_id', Table::TYPE_INTEGER, null, 
                    ['nullable' => false, 'default' => 0,], 'Customer ID'
                )
                ->addColumn(
                        'merchant_customer_id', Table::TYPE_TEXT, 255,
                        ['nullable' => false,'default' => ''], 'Merchant Customer ID'
                ) 
                ->addColumn(
                    'card_id', Table::TYPE_INTEGER, null, 
                    ['nullable' => false, 'default' => 0], 'Card ID'
                )    
                ->addColumn(
                    'card_holder', Table::TYPE_TEXT, 255, 
                    ['nullable' => false, 'default' => ''], 'Card holder'
                )    
                ->addColumn(
                    'card_nickname', Table::TYPE_TEXT, 255, 
                    ['nullable' => false, 'default' => ''], 'Card Nickname'
                )    
                ->addColumn(
                    'card_expiration', Table::TYPE_TEXT, 255, 
                    ['nullable' => false, 'default' => ''], 'Card expiration'
                )    
                ->addColumn(
                    'payment_token', Table::TYPE_TEXT, 255, 
                    ['nullable' => false, 'default' => ''], 'Payment Token'
                )    
                ->addColumn(
                    'last_four_digits', Table::TYPE_TEXT, 4, 
                    ['nullable' => false, 'default' => ''], 'Last four digit'
                )    
                ->addColumn(
                    'profile_id', Table::TYPE_TEXT, 255, 
                    ['nullable' => false, 'default' => ''], 'Profile Id'
                )    
                ->addColumn(
                    'is_deleted', Table::TYPE_SMALLINT, 1, 
                    ['nullable' => false, 'default' => 0], 'Deleted'
                )
                ->addColumn(
                    'created_at', Table::TYPE_TIMESTAMP, null, 
                    ['nullable' => false, 'default' => Table::TIMESTAMP_INIT], 'Created At'
                )
                   
                ->setComment('Creditcard Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table2);
            
            $installer->getConnection()->addIndex(
                $installer->getTable('optimal_creditcard'),
                $setup->getIdxName(
                    $installer->getTable('optimal_creditcard'),
                        ['profile_id', 'card_id', 'merchant_customer_id', 'payment_token','created_at'],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['profile_id', 'card_id', 'merchant_customer_id', 'payment_token','created_at'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        
        $tableName3 = $installer->getTable('optimal_errorcode');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName3) != true) {
            // Create tutorial_simplenews table
            $table3 = $installer->getConnection()
                ->newTable($tableName3)
                ->addColumn('code', Table::TYPE_TEXT, 50, [
                    'nullable' => false,
                    'primary' => true
                        ], 'Error Code'
                )
                ->addColumn(
                        'message', Table::TYPE_TEXT, 255,
                        ['nullable' => false,], 'Error Message'
                )
                ->addColumn(
                        'custom_message', Table::TYPE_TEXT, 255, 
                        ['default' => null], 'Custom Message'
                )
                ->addColumn(
                        'active', Table::TYPE_SMALLINT, 1, 
                        ['nullable' => false,'default' => 0], 'Custom Message'
                )
                ->addColumn(
                        'created_at', Table::TYPE_TIMESTAMP, null,
                        ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                        'Created At'
                )->addColumn(
                        'updated_at', Table::TYPE_TIMESTAMP,null,
                        ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                        'Updated At')    
                ->setComment('Error Message Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table3);
        }
        $installer->endSetup();
    }

}
