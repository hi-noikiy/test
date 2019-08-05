<?php

namespace Celigo\Magento2NetSuiteConnector\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface as ContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface as SchemaInterface;
use Magento\Framework\DB\Ddl\Table;
use \Celigo\Magento2NetSuiteConnector\Helper\Data;
use Monolog\Logger;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * @param Data $helper
     */
    private $helper;

    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }
    
    /**
     * {@inheritdoc}
     */
    public function install(SchemaInterface $setup, ContextInterface $context)
    {
        $setup->startSetup();
        $connection = $setup->getConnection();

        // new table celigo_sales_order.
        $this->helper->createCeligoSalesOrderTable($setup);
        $setup->endSetup();
    }
}
