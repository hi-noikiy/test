<?php
namespace Ktpl\Customreport\Setup;
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        //START: install stuff
        //END:   install stuff
        $connection = $installer->getConnection();
 
            $column = [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                'length' => 6,
                'nullable' => false,
                'comment' => 'Cim order',
                'default' => '0'
            ];
            $connection->addColumn($setup->getTable('sales_order'), 'iscimorder', $column);
        
//START table setup
$table = $installer->getConnection()->newTable(
            $installer->getTable('wholesaler')
    )->addColumn('wholesaler_id',\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null,
            [ 'identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true, ],
            'Entity ID'
        )->addColumn('name',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,null,
            [ 'nullable' => false, ],
            'Name'
        )->addColumn('address',\Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 512,
            [ 'nullable' => false, ],
            'Address'
        )->addColumn('is_active',\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,null,
           [ 'nullable' => false, 'default' => '1', ],
            'Status'
        )->addColumn('creation_time',\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,null,
            [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT, ],
            'Creation Time'
        )->addColumn('update_time', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null,
            [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE, ],
            'Modification Time'
        );
$installer->getConnection()->createTable($table);
//END   table setup
$table2 = $installer->getConnection()->newTable(
            $installer->getTable('sales_flat_cimorder')
    )->addColumn('cimorder_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,null,
            [ 'identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true, ],
            'Entity ID'
        )->addColumn('order_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50,
            [ 'nullable' => false, ],
            'Order id'
        )->addColumn('customer_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,150,
            [ 'nullable' => false, ],
            'Customer Name'
        )->addColumn('telephone', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50,
            [ 'nullable' => false, ],
            'Phone'
        )->addColumn( 'email', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 100,
            [ 'nullable' => false, ],
            'E-mail'
        )->addColumn( 'iscimcustomer', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 4,
            [ 'nullable' => false, ],
            'Cim Customer'
        )->addColumn('product_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,150,
            [ 'nullable' => false, ],
            'Product name'
        )->addColumn('sku', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 150,
            [ 'nullable' => false, ],
            'Sku'
        )->addColumn('attributes', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 150,
            [ 'default' => null, ],
            'Options'
        )->addColumn('dcp', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11,
            [ 'nullable' => true, 'unsigned' => true, ],
            'Dcp'
        )->addColumn('installments', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 100,
            [ 'nullable' => false, ],
            'Installments'
        )->addColumn('monthly', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11,
            [ 'nullable' => false, ],
            'Monthly'
        )->addColumn('deposit', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11,
            [ 'nullable' => true, ],
            'Deposit'
        )->addColumn('cpp', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 4,
            [ 'nullable' => true, ],
            'Cpp'
        )->addColumn('payment', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 100,
            [ 'nullable' => true, ],
            'Payment Method'
        )->addColumn('app_number', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 100,
            [ 'nullable' => true, ],
            'app_number'
        )->addColumn('cimcomment', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null,
            [ 'nullable' => true, ],
            'cimcomment'
        )->addColumn('pgcomment', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null,
            [ 'nullable' => true, ],
            'pgcomment'
        )->addColumn('created_date', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null,
            [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT, ],
            'Creation Time'
        )->addColumn('update_date',\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null,
            [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE, ],
            'Modification Time'
        );
$installer->getConnection()->createTable($table2);

$table3 = $installer->getConnection()->newTable(
            $installer->getTable('sales_flat_pickuporder')
    )->addColumn('pickup_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,null,
            [ 'identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true, ],
            'Entity ID'
        )->addColumn('order_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50,
            [ 'nullable' => false, ],
            'Order id'
        )->addColumn('real_order_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,null,
            [ 'nullable' => false, ],
            'Real order id'
        )->addColumn('customer_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,150,
            [ 'nullable' => false, ],
            'Customer Name'
        )->addColumn('telephone', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50,
            [ 'nullable' => false, ],
            'Phone'
        )->addColumn( 'address', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 512,
            [ 'nullable' => false, ],
            'Address'
        )->addColumn( 'region', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 4,
            [ 'nullable' => true, ],
            'Region'
        )->addColumn('product_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,150,
            [ 'nullable' => false, ],
            'Product name'
        )->addColumn('sku', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 150,
            [ 'nullable' => false, ],
            'Sku'
        )->addColumn('qty', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6,
            [ 'nullable' => false, 'default' => '1', ],
            'Quantity'
        )->addColumn('attributes', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,100,
            [ 'default' => null, ],
            'Options'
        )->addColumn('payment_method', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 100,
            [ 'nullable' => false, ],
            'Payment Method'
        )->addColumn('deposit', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11,
            [ 'nullable' => true, ],
            'Deposit'
        )->addColumn('wholesale_price', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11,
            [ 'nullable' => true, ],
            'Wholesale price'
        )->addColumn('retail_price', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11,
            [ 'nullable' => false, ],
            'Retail price'
        )->addColumn('markup', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50,
            [ 'nullable' => true, ],
            'Markup'
        )->addColumn('wholesaler_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11,
            [ 'nullable' => true, ],
            'Wholesaler id'
        )->addColumn('pickup_address', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null,
            [ 'nullable' => true, ],
            'Pickup address'
        )->addColumn('purchase_order', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 150,
            [ 'nullable' => true, ],
            'Purchase order'
        )->addColumn('pickup', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 4,
            [ 'nullable' => false, 'default' => '0', ],
            'Pickup'
        )->addColumn('pickup_comment', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null,
            [ 'nullable' => true, ],
            'Pickup comment'
        )->addColumn('delivery', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 4,
            [ 'nullable' => true, ],
            'Delivery'
        )->addColumn('delivery_comment', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null,
            [ 'nullable' => true, ],
            'Delivery comment'
        )->addColumn('status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6,
            [ 'nullable' => true, ],
            'Status'
        )->addColumn('po_created', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6,
            [ 'nullable' => false, 'default' => '0' ],
            'Po created'
        )->addColumn('delivery_time', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50,
            [ 'nullable' => true, ],
            'Delivery time'
        )->addColumn('order_created_date', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null,
            [ 'nullable' => false, ],
            'Order created date'
        )->addColumn('created_date', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null,
            [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT, ],
            'Creation Time'
        )->addColumn('update_date',\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null,
            [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE, ],
            'Modification Time'
        );
$installer->getConnection()->createTable($table3);

$table4 = $installer->getConnection()->newTable(
            $installer->getTable('sales_flat_deliveryorder')
    )->addColumn('delivery_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,null,
            [ 'identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true, ],
            'Entity ID'
        )->addColumn('order_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50,
            [ 'nullable' => false, ],
            'Order id'
        )->addColumn('real_order_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,null,
            [ 'nullable' => false, ],
            'Real order id'
        )->addColumn('pickupid', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,null,
            [ 'nullable' => false, ],
            'Pickupid'
        )->addColumn('customer_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,150,
            [ 'nullable' => false, ],
            'Customer Name'
        )->addColumn('telephone', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50,
            [ 'nullable' => false, ],
            'Phone'
        )->addColumn( 'address', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 512,
            [ 'nullable' => false, ],
            'Address'
        )->addColumn( 'region', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 4,
            [ 'nullable' => true, ],
            'Region'
        )->addColumn('product_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,150,
            [ 'nullable' => false, ],
            'Product name'
        )->addColumn('sku', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 150,
            [ 'nullable' => false, ],
            'Sku'
        )->addColumn('qty', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6,
            [ 'nullable' => false, 'default' => '1', ],
            'Quantity'
        )->addColumn('attributes', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,100,
            [ 'default' => null, ],
            'Options'
        )->addColumn('payment_method', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 100,
            [ 'nullable' => false, ],
            'Payment Method'
        )->addColumn('deposit', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 11,
            [ 'nullable' => true, ],
            'Deposit'
        )->addColumn('customer_comment', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null,
            [ 'nullable' => true, ],
            'Customer comment'
        )->addColumn('delivery_comment', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null,
            [ 'nullable' => true, ],
            'Delivery comment'
        )->addColumn('status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6,
            [ 'nullable' => false, 'default' => '0',],
            'Status'
        )->addColumn('delivery_time', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50,
            [ 'nullable' => true, ],
            'Delivery time'
        )->addColumn('delivery_date_time', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null,
            [ 'nullable' => true, ],
            'Delivery date time'
        )->addColumn('order_created_date', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null,
            [ 'nullable' => false, ],
            'Order created date'
        )->addColumn('created_date', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null,
            [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT, ],
            'Creation Time'
        )->addColumn('update_date',\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null,
            [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE, ],
            'Modification Time'
        );
$installer->getConnection()->createTable($table4);

$table5 = $installer->getConnection()->newTable(
            $installer->getTable('sales_invoice_vat')
    )->addColumn('invoice_vat_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,null,
            [ 'identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true, ],
            'Entity ID'
        )->addColumn('invoice_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50,
            [ 'nullable' => false, ],
            'Invoice id'
        )->addColumn('pickup_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,null,
            [ 'nullable' => false, ],
            'Pickup id'
        )->addColumn('order_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,null,
            [ 'nullable' => false, ],
            'Order id'
        )->addColumn('product_name', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,150,
            [ 'nullable' => false, ],
            'Product name'
        )->addColumn('sku', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 150,
            [ 'nullable' => false, ],
            'Sku'
        )->addColumn('qty', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 6,
            [ 'nullable' => false, 'default' => '1', ],
            'Quantity'
        )->addColumn('attributes', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,100,
            [ 'default' => null, ],
            'Options'
        )->addColumn('vatregno', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50,
            [ 'nullable' => true, ],
            'Vatregno'
        )->addColumn('brn', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50,
            [ 'nullable' => true, ],
            'Brn'
        )->addColumn('created_date', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null,
            [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT, ],
            'Creation Time'
        )->addColumn('update_date',\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null,
            [ 'nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE, ],
            'Modification Time'
        );
$installer->getConnection()->createTable($table5);

$installer->endSetup();
    }
}
