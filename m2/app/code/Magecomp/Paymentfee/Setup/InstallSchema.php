<?php
/**
 * Magento Paymentfee extension
 *
 * @category   Magecomp
 * @package    Magecomp_Paymentfee
 * @author     Magecomp
 */
namespace Magecomp\Paymentfee\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $connection = $setup->getConnection();
		
		//Quote Tabel Field Added
		$connection->addColumn(
			$setup->getTable('quote'),
			'mc_paymentfee_amount',
			[
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Paymentfee Amount'
			]
		);
		$connection->addColumn($setup->getTable('quote'),
			'base_mc_paymentfee_amount',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Base Paymentfee Amount'
			]
		);
		$connection->addColumn(
			$setup->getTable('quote'),
			'mc_paymentfee_description',
			[
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'comment' => 'Paymentfee Description'
			]
		);
		
		//Quote Address Tabel Field Added
		$connection->addColumn(
			$setup->getTable('quote_address'),
			'mc_paymentfee_amount',
			[
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Paymentfee Amount'
			]
		);
		$connection->addColumn($setup->getTable('quote_address'),
			'base_mc_paymentfee_amount',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Base Paymentfee Amount'
			]
		);
		$connection->addColumn(
			$setup->getTable('quote_address'),
			'mc_paymentfee_description',
			[
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'comment' => 'Paymentfee Description'
			]
		);
		$connection->addColumn($setup->getTable('quote_address'),
			'mc_paymentfee_tax_amount',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Paymentfee Tax Amount'
			]
		);
		$connection->addColumn($setup->getTable('quote_address'),
			'base_mc_paymentfee_tax_amount',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Base Paymentfee Tax Amount'
			]
		);
		
		
		//Sales Order Tabel Field Added
		$connection->addColumn($setup->getTable('sales_order'),
			'base_mc_paymentfee_amount',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Base Paymentfee Amount'
			]
		);
		$connection->addColumn(
			$setup->getTable('sales_order'),
			'mc_paymentfee_amount',
			[
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Paymentfee Amount'
			]
		);
		$connection->addColumn(
			$setup->getTable('sales_order'),
			'mc_paymentfee_description',
			[
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
				'comment' => 'Paymentfee Description'
			]
		);
		$connection->addColumn($setup->getTable('sales_order'),
			'base_mc_paymentfee_tax_amount',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Base Paymentfee Tax Amount'
			]
		);
		$connection->addColumn($setup->getTable('sales_order'),
			'mc_paymentfee_tax_amount',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Paymentfee Tax Amount'
			]
		);
		$connection->addColumn($setup->getTable('sales_order'),
			'mc_paymentfee_amount_invoiced',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Paymentfee Invoice Amount'
			]
		);
		$connection->addColumn($setup->getTable('sales_order'),
			'base_mc_paymentfee_amount_invoiced',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Base Paymentfee Invoice Amount'
			]
		);
		$connection->addColumn($setup->getTable('sales_order'),
			'mc_paymentfee_amount_refunded',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Paymentfee Refund Amount'
			]
		);
		$connection->addColumn($setup->getTable('sales_order'),
			'base_mc_paymentfee_amount_refunded',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Base Paymentfee Refund Amount'
			]
		);
		$connection->addColumn($setup->getTable('sales_order'),
			'mc_paymentfee_tax_amount_invoiced',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Paymentfee Invoice Tax Amount'
			]
		);
		$connection->addColumn($setup->getTable('sales_order'),
			'base_mc_paymentfee_tax_amount_invoiced',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Base Paymentfee Invoice Tax Amount'
			]
		);
		$connection->addColumn($setup->getTable('sales_order'),
			'mc_paymentfee_tax_amount_refunded',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Paymentfee Invoice Tax Refund Amount'
			]
		);
		$connection->addColumn($setup->getTable('sales_order'),
			'base_mc_paymentfee_tax_amount_refunded',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Base Paymentfee Invoice Tax Refund Amount'
			]
		);
		
		//Sales Invoice Tabel Field Added
		$connection->addColumn($setup->getTable('sales_invoice'),
			'mc_paymentfee_amount',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Paymentfee Amount'
			]
		);
		$connection->addColumn($setup->getTable('sales_invoice'),
			'base_mc_paymentfee_amount',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Base Paymentfee Amount'
			]
		);
		$connection->addColumn($setup->getTable('sales_invoice'),
			'mc_paymentfee_tax_amount',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Paymentfee Tax Amount'
			]
		);
		$connection->addColumn($setup->getTable('sales_invoice'),
			'base_mc_paymentfee_tax_amount',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Base Paymentfee Tax Amount'
			]
		);
		
		//Sales Creditmemo Tabel Field Added
		$connection->addColumn($setup->getTable('sales_creditmemo'),
			'mc_paymentfee_amount',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Paymentfee Amount'
			]
		);
		$connection->addColumn($setup->getTable('sales_creditmemo'),
			'base_mc_paymentfee_amount',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Base Paymentfee Amount'
			]
		);
		$connection->addColumn($setup->getTable('sales_creditmemo'),
			'mc_paymentfee_tax_amount',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Paymentfee Tax Amount'
			]
		);
		$connection->addColumn($setup->getTable('sales_creditmemo'),
			'base_mc_paymentfee_tax_amount',
			[	
				'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
				'length' => '12,4',
				'nullable' => false, 
				'default' => '0.0000',
				'comment' => 'Base Paymentfee Tax Amount'
			]
		);
    }
}