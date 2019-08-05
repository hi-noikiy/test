<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Celigo\Magento2NetSuiteConnector\Model\ResourceModel\CeligoSalesOrder;

use \Celigo\Magento2NetSuiteConnector\Model\ResourceModel\CeligoSalesOrder\Collection;

/**
 * Class CollectionFactory
 */
class CollectionFactory implements CollectionFactoryInterface
{
    /**
     * Celigo Sales Order Collection instance
     *
     * @var \Celigo\Magento2NetSuiteConnector\Model\ResourceModel\CeligoSalesOrder\Collection
     */
    private $collection = null;

    /**
     * resourceConnection to get table name
     *
     * @var string
     */
    private $resourceConnection = null;

    /**
     * Factory constructor
     *
     * @param Collection $collection
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        Collection $collection,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->collection = $collection;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * {@inheritdoc}
     */
    public function createSalesOrderCollection($defaultFilters = [])
    {
        $this->collection->join(
            ['t' => $this->resourceConnection->getTableName('sales_order')],
            'main_table.parent_id = t.entity_id'
        );

        // To add default magento filters.
        foreach ($defaultFilters as $filter) {
            $this->collection->addFieldToFilter("t.{$filter['field']}", [$filter['type'] => $filter['value']]);
        }

        return $this->collection;
    }

    /**
     * {@inheritdoc}
     */
    public function insertCeligoSalesOrder($insertData = [])
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('celigo_sales_order');
        if ($connection->isTableExists($tableName)) {
            foreach ($insertData as $rowData) {
                $connection->insert($tableName, $rowData);
            }
        } else {
            $this->logger->addInfo(
                'Celigo_Magento2NetSuiteConnector : insertCeligoSalesOrder : celigo_sales_order table no exists.'
            );
        }
    }
}
