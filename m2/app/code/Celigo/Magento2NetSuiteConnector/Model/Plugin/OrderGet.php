<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Celigo\Magento2NetSuiteConnector\Model\Plugin;

use \Magento\Framework\Exception\NoSuchEntityException;
use \Celigo\Magento2NetSuiteConnector\Logger\Logger;

class OrderGet
{
    /** @var \Celigo\Magento2NetSuiteConnector\Api\CeligoOrderRepositoryInterface */
    private $celigoOrderRepository;

    /** @var \Magento\Sales\Api\Data\OrderExtensionFactory */
    private $orderExtensionFactory;
    
    private $logger;

    /**
     * Init plugin
     *
     * @param \Celigo\Magento2NetSuiteConnector\Api\CeligoOrderRepositoryInterface $celigoOrderRepository
     * @param \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory
     */
    public function __construct(
        \Celigo\Magento2NetSuiteConnector\Api\CeligoOrderRepositoryInterface $celigoOrderRepository,
        \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory,
        Logger $logger
    ) {
        $this->celigoOrderRepository = $celigoOrderRepository;
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->logger = $logger;
    }

    /**
     * Get CeligoSalesOrder
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $resultOrder
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $resultOrder
    ) {
        $resultOrder = $this->getCeligoSalesOrder($resultOrder);

        return $resultOrder;
    }

    /**
     * Get gift message for order
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getCeligoSalesOrder(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getCeligoSalesOrder()) {
            return $order;
        }
        
        $this->logger->addInfo("Order id" . $order->getEntityId());
        try {
            /** @var \Celigo\Magento2NetSuiteConnector\Api\Data\CeligoSalesOrderInterface $celigoSalesOrder */
            $celigoSalesOrder = $this->celigoOrderRepository->get($order->getEntityId());
            $this->logger->addInfo("celigoSalesOrder" . $celigoSalesOrder->getParentId());
        } catch (NoSuchEntityException $e) {
            $this->logger->addError("in exception" . $e);
            return $order;
        }

        /** @var \Magento\Sales\Api\Data\OrderExtension $orderExtension */
        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();
        $orderExtension->setCeligoSalesOrder($celigoSalesOrder);
        $order->setExtensionAttributes($orderExtension);

        return $order;
    }

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $resultOrder
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Model\ResourceModel\Order\Collection $resultOrder
    ) {
        /** @var  $order */
        foreach ($resultOrder->getItems() as $order) {
            $this->afterGet($subject, $order);
        }
        return $resultOrder;
    }
}
