<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Plugin\Model\Service;

use Aheadworks\StoreCredit\Api\CustomerStoreCreditManagementInterface;
use Magento\Sales\Model\Service\CreditmemoService;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CreditmemoServicePlugin
 *
 * @package Aheadworks\StoreCredit\Plugin\Model\Service
 */
class CreditmemoServicePlugin
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
     * @param CustomerStoreCreditManagementInterface $customerStoreCreditService
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CustomerStoreCreditManagementInterface $customerStoreCreditService,
        StoreManagerInterface $storeManager
    ) {
        $this->customerStoreCreditService = $customerStoreCreditService;
        $this->storeManager = $storeManager;
    }

    /**
     * Refund Store Credit to customer on credit memo
     *
     * @param  CreditmemoService $subject
     * @param  CreditmemoInterface $result
     * @return CreditmemoInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRefund(CreditmemoService $subject, CreditmemoInterface $result)
    {
        if ($result->getCustomerId()) {
            $websiteId = $this->storeManager->getStore($result->getStoreId())->getWebsiteId();
            if ($result->getBaseAwStoreCreditRefunded()) {
                $this->customerStoreCreditService->refundToStoreCredit(
                    $result->getCustomerId(),
                    $result->getBaseAwStoreCreditRefunded(),
                    $result->getOrderId(),
                    $result,
                    $websiteId
                );
            }
            if ($result->getBaseAwStoreCreditReimbursed()) {
                $this->customerStoreCreditService->reimbursedSpentStoreCredit(
                    $result->getCustomerId(),
                    $result->getBaseAwStoreCreditReimbursed(),
                    $result->getOrderId(),
                    $result,
                    $websiteId
                );
            }
        }
        return $result;
    }
}
