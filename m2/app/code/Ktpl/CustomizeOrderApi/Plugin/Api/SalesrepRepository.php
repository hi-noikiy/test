<?php

namespace Ktpl\CustomizeOrderApi\Plugin\Api;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Ktpl\CustomizeOrderApi\Api\SalesrepRepositoryInterface;

class SalesrepRepository
{
    /**
     * Order Extension Attributes Factory
     *
     * @var OrderExtensionFactory
     */
    protected $extensionFactory;

    protected $salesrepRepository;

    /**
     * OrderRepositoryPlugin constructor
     *
     * @param OrderExtensionFactory $extensionFactory
     */
    public function __construct(
        OrderExtensionFactory $extensionFactory,
        SalesrepRepositoryInterface $salesrepRepository
        )
    {
        $this->extensionFactory = $extensionFactory;
        $this->salesrepRepository = $salesrepRepository;
    }

    /**
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order)
    {
        $ktplsalesrepData = $this->salesrepRepository->getByOrderId($order->getEntityId());
        $extensionAttributes = $order->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
        $extensionAttributes->setKtplSalesrep($ktplsalesrepData);
        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }

    /**
     *
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     *
     * @return OrderSearchResultInterface
     */
    public function afterGetList(OrderRepositoryInterface $subject, OrderSearchResultInterface $searchResult)
    {
        $orders = $searchResult->getItems();
        foreach ($orders as &$order) {
            $ktplsalesrepData = $this->salesrepRepository->getByOrderId($order->getEntityId());
            $extensionAttributes = $order->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
            $extensionAttributes->setKtplSalesrep($ktplsalesrepData);
            $order->setExtensionAttributes($extensionAttributes);
        }

        return $searchResult;
    }
}