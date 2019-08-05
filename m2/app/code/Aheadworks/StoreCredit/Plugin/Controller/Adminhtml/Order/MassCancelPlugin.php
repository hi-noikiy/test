<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Plugin\Controller\Adminhtml\Order;

use Aheadworks\StoreCredit\Api\CustomerStoreCreditManagementInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Controller\Adminhtml\Order\MassCancel;

/**
 * Class MassCancelPlugin
 * @package Aheadworks\StoreCredit\Plugin\Controller\Adminhtml\Order
 */
class MassCancelPlugin
{
    /**
     * @var CustomerStoreCreditManagementInterface
     */
    private $customerStoreCreditService;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param CustomerStoreCreditManagementInterface $customerStoreCreditService
     * @param StoreManagerInterface $storeManager
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CustomerStoreCreditManagementInterface $customerStoreCreditService,
        StoreManagerInterface $storeManager,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->customerStoreCreditService = $customerStoreCreditService;
        $this->storeManager = $storeManager;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Reimburse Store Credit by massCancel action
     *
     * @param MassCancel $subject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute($subject)
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        foreach ($collection->getItems() as $order) {
            if (!$order->canCancel()) {
                continue;
            }
            if ($order->getCustomerId() && $order->getAwUseStoreCredit()) {
                $websiteId = $this->storeManager->getStore($order->getStoreId())->getWebsiteId();
                $this->customerStoreCreditService->reimbursedSpentStoreCreditOrderCancel(
                    $order->getCustomerId(),
                    abs($order->getBaseAwStoreCreditAmount()),
                    $order,
                    $websiteId
                );
            }
        }
    }
}
