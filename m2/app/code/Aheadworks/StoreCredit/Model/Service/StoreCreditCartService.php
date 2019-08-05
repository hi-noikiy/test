<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model\Service;

use Aheadworks\StoreCredit\Api\StoreCreditCartManagementInterface;
use Aheadworks\StoreCredit\Api\CustomerStoreCreditManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Aheadworks\StoreCredit\Model\StoreCreditCartService
 */
class StoreCreditCartService implements StoreCreditCartManagementInterface
{
    /**
     * @var CustomerStoreCreditManagementInterface
     */
    private $customerStoreCreditService;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @param CustomerStoreCreditManagementInterface $customerStoreCreditService
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        CustomerStoreCreditManagementInterface $customerStoreCreditService,
        CartRepositoryInterface $quoteRepository
    ) {
        $this->customerStoreCreditService = $customerStoreCreditService;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     *  {@inheritDoc}
     */
    public function get($cartId)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }
        return $quote->getAwUseStoreCredit();
    }

    /**
     *  {@inheritDoc}
     */
    public function set($cartId)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }

        if (!$quote->getCustomerId()
            || !(float)$this->customerStoreCreditService
            ->getCustomerStoreCreditBalance($quote->getCustomerId())
        ) {
            throw new NoSuchEntityException(__('No Store Credit to be used'));
        }

        $quote->getShippingAddress()->setCollectShippingRates(true);
        try {
            $quote->setAwUseStoreCredit(true);
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not apply Store Credit'));
        }
        return true;
    }

    /**
     *  {@inheritDoc}
     */
    public function remove($cartId)
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }

        $quote->getShippingAddress()->setCollectShippingRates(true);
        try {
            $quote->setAwUseStoreCredit(false);
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not remove Store Credit'));
        }
        return true;
    }
}
