<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Celigo\Magento2NetSuiteConnector\Model;

use \Magento\Framework\Exception\CouldNotSaveException;
use \Magento\Framework\Exception\InputException;
use \Magento\Framework\Exception\State\InvalidTransitionException;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Celigo\Magento2NetSuiteConnector\Logger\Logger;
use \Celigo\Magento2NetSuiteConnector\Model\CeligoSalesOrderFactory;

/**
 * Celigo sales order repository object.
 */
class CeligoOrderRepository implements \Celigo\Magento2NetSuiteConnector\Api\CeligoOrderRepositoryInterface
{
    /**
     * Order factory.
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    private $logger;

    private $celigoSalesOrderFactory;

    /**
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Celigo\Magento2NetSuiteConnector\Model\CeligoSalesOrder $celigoSalesOrder
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        Logger $logger,
        CeligoSalesOrderFactory $celigoSalesOrderFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->logger = $logger;
        $this->celigoSalesOrderFactory = $celigoSalesOrderFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function get($orderId)
    {
        return $this->celigoSalesOrderFactory->create()->load($orderId, 'parent_id');
    }

    /**
     *
     * @param \Celigo\Magento2NetSuiteConnector\Api\Data\CeligoSalesOrderInterface $celigoSalesOrder
     * @return \Celigo\Magento2NetSuiteConnector\Api\Data\CeligoSalesOrderInterface $celigoSalesOrder
     */
    public function update($celigoSalesOrder)
    {
        try {
            $celigoSalesOrderOld = $this->celigoSalesOrderFactory->create()->load(
                $celigoSalesOrder->getParentId(),
                'parent_id'
            );
        } catch (\Exception $e) {
            $this->logger->addError('Could not find Celigo Sales Order: '. $e->getMessage());
        }
        try {
            $celigoSalesOrderOld->setIsExportedToIO($celigoSalesOrder->getIsExportedToIO());
            $celigoSalesOrderOld->setParentId($celigoSalesOrder->getParentId());
            $celigoSalesOrderOld->save();
        } catch (\Exception $e) {
            $this->logger->addError('Could not save Celigo Sales Order: '. $e->getMessage());
        }

        return $celigoSalesOrder;
    }
}
