<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Celigo\Magento2NetSuiteConnector\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use \Celigo\Magento2NetSuiteConnector\Logger\Logger;
use \Celigo\Magento2NetSuiteConnector\Helper\Data;
use \Magento\Framework\Registry;
use \Magento\Framework\Model\ResourceModel\Iterator;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param Logger $logger
     * @param CollectionFactory $orderCollectionFactory
     * @param Session $registry
     * @param Data $helper
     */
    private $orderCollectionFactory;
    private $logger;
    private $registry;
    private $helper;
    private $iterator;

    public function __construct(
        CollectionFactory $orderCollectionFactory,
        Logger $logger,
        Registry $registry,
        Data $helper,
        Iterator $iterator
    ) {
        $this->logger      = $logger;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->registry = $registry;
        $this->helper = $helper;
        $this->iterator = $iterator;
        $this->insertData = [];
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        
        $installer = $setup;
 
        $installer->startSetup();
        
        if (version_compare($context->getVersion(), '1.3.1', '<')) {
            try {
                // new table celigo_sales_order.
                $this->helper->createCeligoSalesOrderTable($installer);

                if ($installer->getConnection()->tableColumnExists(
                    $setup->getTable('sales_order'),
                    'is_exported_to_io'
                )
                ) {
                    $this->logger->addInfo(
                        'Celigo_Magento2NetSuiteConnector : UpdateSchema : sales_order.is_exported_to_io exists'
                    );
                    
                    $collection = $this->orderCollectionFactory->create();
                    $collection->addFieldToSelect(['entity_id', 'is_exported_to_io', 'created_at', 'updated_at']);

                    $this->logger->addInfo(
                        'Celigo_Magento2NetSuiteConnector : UpdateSchema : query' . (string) $collection->getSelect()
                    );
                    
                    $this->iterator->walk($collection->getSelect(), [[$this, 'celigoOrderDataCallback']]);
                    
                    // removing is_exported_to_io column from sales_order table.
                    $installer->getConnection()->dropColumn(
                        $installer->getTable('sales_order'),
                        'is_exported_to_io'
                    );

                    $this->logger->addInfo(
                        'Celigo_Magento2NetSuiteConnector : UpdateSchema : sales_order.is_exported_to_io removed'
                    );
                }
                
                // Registry data is preserved only for that request and not for all sustained for long.
                $this->registry->register('celigo_temp_data', $this->insertData);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->registry->unregister('celigo_temp_data');
            }
        }
 
        $installer->endSetup();
    }

    /**
     * Iterate sales order data to process as callback called.
     * @return null
     */
    public function celigoOrderDataCallback($args)
    {
        $orderRow = $args['row'];
        $this->insertData[] = [
            'parent_id' => $orderRow['entity_id'],
            'is_exported_to_io' => $orderRow['is_exported_to_io'],
            'created_at' => $orderRow['created_at'],
            'updated_at' => $orderRow['updated_at']
        ];
    }
}
