<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Celigo\Magento2NetSuiteConnector\Model\ResourceModel\CeligoSalesOrder;

/**
 * Class CollectionFactoryInterface
 */
interface CollectionFactoryInterface
{
    /**
     * Create class instance join with sales order collection
     *
     * @return \Celigo\Magento2NetSuiteConnector\Model\ResourceModel\CeligoSalesOrder\Collection
     */
    public function createSalesOrderCollection($defaultFilters = []);

    /**
     * Insert data in celigo sales order collection
     *
     * @return \Celigo\Magento2NetSuiteConnector\Model\ResourceModel\CeligoSalesOrder\Collection
     */
    public function insertCeligoSalesOrder($insertData = []);
}
