<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Celigo\Magento2NetSuiteConnector\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use \Celigo\Magento2NetSuiteConnector\Logger\Logger;
use \Magento\Framework\Registry;
use Celigo\Magento2NetSuiteConnector\Model\ResourceModel\CeligoSalesOrder\CollectionFactory;

class UpgradeData implements UpgradeDataInterface
{

    /**
     * @param Logger $logger
     * @param Session $registry
     */
    private $logger;
    private $registry;

    /**
     * @var celigoSalesOrderCollection
     */
    private $celigoSalesOrderCollection;

    public function __construct(Logger $logger, Registry $registry, CollectionFactory $celigoSalesOrderCollection)
    {
        $this->logger = $logger;
        $this->registry = $registry;
        $this->celigoSalesOrderCollection = $celigoSalesOrderCollection;
    }
    
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $connection = $setup->getConnection();

        if (version_compare($context->getVersion(), '1.3.1', '<')) {
            $insertData = $this->registry->registry('celigo_temp_data');
                       
            if (!empty($insertData)) {
                try {
                    $this->celigoSalesOrderCollection->insertCeligoSalesOrder($insertData);

                    $this->logger->addInfo(
                        'Celigo_Magento2NetSuiteConnector : UpgradeData : celigo_sales_order data inserted'
                    );
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage());
                    $this->registry->unregister('celigo_temp_data');
                }
            }

            $this->registry->unregister('celigo_temp_data');
        }
        
        $setup->endSetup();
    }
}
