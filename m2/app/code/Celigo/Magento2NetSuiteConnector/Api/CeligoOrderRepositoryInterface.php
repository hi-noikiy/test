<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Celigo\Magento2NetSuiteConnector\Api;

/**
 * Interface CeligoOrderRepositoryInterface
 * @api
 */
interface CeligoOrderRepositoryInterface
{
    /**
     * Return the gift message for a specified order.
     *
     * @param int $orderId The order ID.
     * @return \Celigo\Magento2NetSuiteConnector\Api\Data\CeligoSalesOrderInterface Celigo Sales Order.
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($orderId);

    /**
     * @api
     *
     * @param Celigo\Magento2NetSuiteConnector\Api\Data\CeligoSalesOrderInterface $celigoSalesOrder
     * @return Celigo\Magento2NetSuiteConnector\Api\Data\CeligoSalesOrderInterface
     */
    public function update($celigoSalesOrder);
}
