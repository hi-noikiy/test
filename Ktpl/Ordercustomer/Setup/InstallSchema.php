<?php
namespace Ktpl\Ordercustomer\Setup;
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        //START: install stuff
        //END:   install stuff
      
//START table setup
$table = $installer->getConnection()->newTable(
            $installer->getTable('ordercustomer')
    )->addColumn('ordercustomer_id',\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null,
            [ 'identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true, ],
            'Entity ID'
        )->addColumn('increment_id',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,32,
            [ 'nullable' => false, ],
            'Order Id'
        )->addColumn('order_created_date',\Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,null,
            [ 'nullable' => true, ],
            'Order Date'
        )->addColumn('username',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255,
            [ 'nullable' => false, ],
            'Username'
        )->addColumn('customer_name',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255,
            [ 'nullable' => false, ],
            'Customer Name'
        )->addColumn('product_name',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 512,
            [ 'nullable' => false, ],
            'Product Name'
        )->addColumn('product_subtitle',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,255,
           [ 'nullable' => false, ],
            'Product Subtitle'
        )->addColumn('customtitle',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,255,
           [ 'nullable' => false, ],
            'Customtitle'
        )->addColumn('product_sku',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,255,
           [ 'nullable' => false, ],
            'Product Sku'
        )->addColumn('price',\Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,'12,4',
           [ 'nullable' => false, 'default'=> 0],
            'Product Price'
         )->addColumn('payment_type',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,255,
           [ 'nullable' => false, ],
            'Payment'        
         )->addColumn('invoice_comment',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,null,
           [ 'nullable' => true, ],
            'Comment'        
         )->addColumn('created_time',\Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,null,
           [ 'nullable' => true, ],
            'Comment'        
        )->addColumn('creation_time',\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,null,
            [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT, ],
            'Creation Time'
        )->addColumn('update_time', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null,
            [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE, ],
            'Modification Time'
        )
        ->addForeignKey(
                        $installer->getFkName('ordercustomer_order_id', 'increment_id', 'sales_order', 'increment_id'),
                        'increment_id',
                        $installer->getTable('sales_order'),
                        'increment_id',
                        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                       )
                            ->setComment('ordercustomer order id');
$installer->getConnection()->createTable($table);
//END   table setup

$installer->endSetup();
    }
}
