<?php
namespace Ktpl\Repaircenter\Setup;
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
            $installer->getTable('repair_to_center')
    )->addColumn('repair_id',\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null,
            [ 'identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true, ],
            'Entity ID'
        )->addColumn('increment_id',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,50,
            [ 'nullable' => false, ],
            'Order Id'
        )->addColumn('created_time',\Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,null,
            [ 'nullable' => true, ],
            'Order Date'        
        )->addColumn('customer',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null,
            [ 'nullable' => false, ],
            'Customer name'
        )->addColumn('product',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null,
            [ 'nullable' => false, ],
            'Product Name'
        )->addColumn('problem_description',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null,
            [ 'nullable' => false, ],
            'Problem'
        )->addColumn('wholesaler',\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null,
           [ 'nullable' => false, ],
            'Wholesaler'
        )->addColumn('serial_no',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,null,
           [ 'nullable' => false, ],
            'Serial no'
        )->addColumn('pickup_option',\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,4,
           [ 'nullable' => false, ],
            'Pickup'
        )->addColumn('pickup_address',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null,
           [ 'nullable' => false, ],
            'Pickup Address'
         )->addColumn('is_pickup',\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,4,
           [ 'nullable' => false, ],
            'is pickup'        
         )->addColumn('pickup_date',\Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,null,
           [ 'nullable' => true, ],
            'Pickup date'        
         )->addColumn('status',\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,4,
           [ 'nullable' => true, ],
            'Status'        
        )->addColumn('creation_time',\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,null,
            [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT, ],
            'Creation Time'
        )->addColumn('update_time', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null,
            [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE, ],
            'Modification Time'
        )
        ->addForeignKey(
                        $installer->getFkName('repair_center_order_id', 'increment_id', 'sales_order', 'increment_id'),
                        'increment_id',
                        $installer->getTable('sales_order'),
                        'increment_id',
                        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                       )
                            ->setComment('repair_center order id');
$installer->getConnection()->createTable($table);
//END   table setup

$table2 = $installer->getConnection()->newTable(
            $installer->getTable('repair_to_customer')
    )->addColumn('repair_customer_id',\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null,
            [ 'identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true, ],
            'Entity ID'
        )->addColumn('repair_center_id',\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null,
            [ 'nullable' => false, 'unsigned' => true, ],
            'Repair center Id'
        )->addColumn('customer',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null,
            [ 'nullable' => false, ],
            'Customer name'
        )->addColumn('product',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null,
            [ 'nullable' => false, ],
            'Product Name'
        )->addColumn('service_order_no',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null,
            [ 'nullable' => true, ],
            'Service order No'
        )->addColumn('dispatch_date',\Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null,
           [ 'nullable' => true, ],
            'Dispatch Date'
        )->addColumn('diagnostic',\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,4,
           [ 'nullable' => true, ],
            'Diagnostic'
        )->addColumn('supplier_comments',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null,
           [ 'nullable' => true, ],
            'Supplier Comments'
        )->addColumn('warranty_status',\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,4,
           [ 'nullable' => true, ],
            'Warranty Status'
         )->addColumn('client_informed',\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,4,
           [ 'nullable' => true, ],
            'Client Informed'        
         )->addColumn('comments',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null,
           [ 'nullable' => true, ],
            'Comments'        
         )->addColumn('supplier_informed',\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,4,
           [ 'nullable' => true, ],
            'Supplier Informed'        
         )->addColumn('replacement',\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,4,
           [ 'nullable' => true, ],
            'Replacement'        
         )->addColumn('collect_date',\Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null,
           [ 'nullable' => true, ],
            'Collect Date'        
         )->addColumn('leadtime',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 512,
           [ 'nullable' => false, ],
            'Leadtime'        
         )->addColumn('is_pickup',\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,4,
           [ 'nullable' => true, ],
            'Is Pickup'        
         )->addColumn('status',\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,4,
           [ 'nullable' => true, ],
            'Status'        
        )->addColumn('creation_time',\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,null,
            [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT, ],
            'Creation Time'
        )->addColumn('update_time', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null,
            [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE, ],
            'Modification Time'
        )
        ->addForeignKey(
                        $installer->getFkName('repair_customer_repair_center_id', 'repair_center_id', 'repair_to_center', 'repair_id'),
                        'repair_center_id',
                        $installer->getTable('repair_to_center'),
                        'repair_id',
                        \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                       )
                            ->setComment('repair_to_customer repair id');
$installer->getConnection()->createTable($table2);
$installer->endSetup();
    }
}
