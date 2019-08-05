<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model\Service;

use Aheadworks\StoreCredit\Api\Data\TransactionInterface;
use Aheadworks\StoreCredit\Api\TransactionRepositoryInterface;
use Aheadworks\StoreCredit\Api\TransactionManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\StoreCredit\Model\Source\NotifiedStatus;

/**
 * Class Aheadworks\StoreCredit\Model\Service\TransactionService
 */
class TransactionService implements TransactionManagementInterface
{
    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param TransactionRepositoryInterface $transactionRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->storeManager = $storeManager;
    }

    /**
     *  {@inheritDoc}
     */
    public function createEmptyTransaction()
    {
        return $this->transactionRepository->create();
    }

    /**
     *  {@inheritDoc}
     */
    public function createTransaction(
        CustomerInterface $customer,
        $balance,
        $type,
        $commentToCustomer = null,
        $commentToCustomerPlaceholder = null,
        $commentToAdmin = null,
        $websiteId = null,
        $balanceUpdateNotified = NotifiedStatus::NO,
        $arguments = [],
        $adminUserId = null
    ) {
        /** @var $transaction TransactionInterface **/
        $transaction = $this->createEmptyTransaction();

        $transaction->setCustomerId($customer->getId());
        $transaction->setCustomerEmail($customer->getEmail());
        $transaction->setCustomerName($this->getCustomerName($customer));

        $websiteId = $websiteId ? : $this->storeManager->getStore()->getWebsiteId();
        $transaction->setWebsiteId($websiteId);
        $transaction->setType($type);

        $transaction->setBalance($balance);
        $transaction->setCommentToCustomer($commentToCustomer);
        $transaction->setCommentToCustomerPlaceholder($commentToCustomerPlaceholder);
        $transaction->setCommentToAdmin($commentToAdmin);
        $transaction->setBalanceUpdateNotified($balanceUpdateNotified);
        $transaction->setCreatedBy($adminUserId);

        return $this->saveTransaction($transaction, $arguments);
    }

    /**
     *  {@inheritDoc}
     */
    public function saveTransaction(TransactionInterface $transaction, $arguments = [])
    {
        $result = false;
        try {
            $result = $this->transactionRepository->save($transaction, $arguments);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $result;
    }

    /**
     *  {@inheritDoc}
     */
    public function updateNotifiedStatus($transactionId, $balanceUpdateNotified)
    {
        $result = false;
        try {
            $transaction = $this->transactionRepository->getById($transactionId);
            $transaction->setBalanceUpdateNotified($balanceUpdateNotified);
            $result = $this->transactionRepository->save($transaction);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $result;
    }

    /**
     *  {@inheritDoc}
     */
    public function updateCurrentBalance($transactionId, $balance)
    {
        $result = false;
        try {
            $transaction = $this->transactionRepository->getById($transactionId);
            $transaction->setCurrentBalance($balance);
            $result = $this->transactionRepository->save($transaction);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $result;
    }

    /**
     * Retrieve customer full name
     *
     * @param  CustomerInterface $customer
     * @return string
     */
    private function getCustomerName(CustomerInterface $customer)
    {
        return $customer->getFirstname() . ' ' . $customer->getLastname();
    }
}
