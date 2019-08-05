<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Aheadworks\StoreCredit\Api\CustomerStoreCreditManagementInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class ProcessOrderCreate
 *
 * @package Aheadworks\StoreCredit\Observer
 */
class ProcessOrderCreate implements ObserverInterface
{
    /**
     * @var CustomerStoreCreditManagementInterface
     */
    private $customerStoreCreditService;

    /**
     * @param CustomerStoreCreditManagementInterface $customerStoreCreditService
     */
    public function __construct(
        CustomerStoreCreditManagementInterface $customerStoreCreditService
    ) {
        $this->customerStoreCreditService = $customerStoreCreditService;
    }

    /**
     * Apply store credit for admin checkout
     *
     * @param Observer $observer
     * @return $this
     * @throws NoSuchEntityException No Store Credit to be used
     */
    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getOrderCreateModel()->getQuote();
        $request = $observer->getEvent()->getRequest();

        if (isset($request['payment']) && isset($request['payment']['aw_use_store_credit'])) {
            $awUseStoreCredit = (bool)$request['payment']['aw_use_store_credit'];
            if ($awUseStoreCredit && (!$quote->getCustomerId()
                || !$this->customerStoreCreditService->getCustomerStoreCreditBalance($quote->getCustomerId()))
            ) {
                throw new NoSuchEntityException(__('No Store Credit to be used'));
            }

            $quote->setAwUseStoreCredit($awUseStoreCredit);
        }

        return $this;
    }
}
