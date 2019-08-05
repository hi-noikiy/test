<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model\Service;

use Aheadworks\StoreCredit\Api\Data\SummaryInterface;
use Aheadworks\StoreCredit\Api\Data\TransactionInterface;
use Aheadworks\StoreCredit\Api\SummaryRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DataObject;
use Aheadworks\StoreCredit\Model\Config;
use Aheadworks\StoreCredit\Model\Source\SubscribeStatus;
use Aheadworks\StoreCredit\Model\Source\TransactionType;

/**
 * Class Aheadworks\StoreCredit\Model\Service\SummaryService
 */
class SummaryService
{
    /**
     * @var SummaryRepositoryInterface
     */
    private $storeCreditSummaryRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var []
     */
    private $summaryCache;

    /**
     * @param SummaryRepositoryInterface $storeCreditSummaryRepository
     * @param StoreManagerInterface $storeManager
     * @param Config $config
     */
    public function __construct(
        SummaryRepositoryInterface $storeCreditSummaryRepository,
        StoreManagerInterface $storeManager,
        Config $config
    ) {
        $this->storeCreditSummaryRepository = $storeCreditSummaryRepository;
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * Retrieve customer Store Credit balance
     *
     * @param int $customerId
     * @return float
     */
    public function getCustomerStoreCreditBalance($customerId)
    {
        $summary = $this->getStoreCreditSummary($customerId);
        return $summary->getBalance();
    }

    /**
     * Retrieve customer is balance update notification status
     *
     * @param int $customerId
     * @param int $websiteId
     * @return int
     */
    public function getCustomerBalanceUpdateNotificationStatus($customerId, $websiteId = null)
    {
        $summary = $this->getStoreCreditSummary($customerId);
        $defaultBalanceUpdateNotification = $this->config->isSubscribeCustomersToNotificationsByDefault($websiteId)
            ? SubscribeStatus::SUBSCRIBED
            : SubscribeStatus::NOT_SUBSCRIBED;
        return $summary->getBalanceUpdateNotificationStatus() == null
            ? $defaultBalanceUpdateNotification
            : $summary->getBalanceUpdateNotificationStatus();
    }

    /**
     * Add Store Credit summary to customer after each transaction
     *
     * @param TransactionInterface $transaction
     * @return boolean
     * @throws CouldNotSaveException
     */
    public function addSummaryToCustomer(TransactionInterface $transaction)
    {
        $summary = $this->setupSummary($transaction, true);
        return $this->saveSummary($summary);
    }

    /**
     * Update customer summary
     *
     * @param DataObject $data
     * @return boolean
     * @throws CouldNotSaveException
     */
    public function updateCustomerSummary($data)
    {
        $summary = $this->setupSummary($data);
        return $this->saveSummary($summary);
    }

    /**
     * Retrieve Store Credit summary instance
     *
     * @param int $customerId
     * @return SummaryInterface
     */
    private function getStoreCreditSummary($customerId)
    {
        if (isset($this->summaryCache[$customerId])) {
            return $this->summaryCache[$customerId];
        }

        try {
            $summary = $this->storeCreditSummaryRepository->get($customerId);
        } catch (NoSuchEntityException $e) {
            $summary = $this->storeCreditSummaryRepository->create();
        }
        $this->summaryCache[$customerId] = $summary;

        return $summary;
    }

    /**
     * Setup Store Credit summary data model
     *
     * @param TransactionInterface|DataObject $data
     * @param bool $isTransaction
     * @return SummaryInterface
     */
    private function setupSummary($data, $isTransaction = false)
    {
        $customerId = $data->getCustomerId();
        $websiteId = $data->getWebsiteId();

        /** @var $summary SummaryInterface **/
        $summary = $this->getStoreCreditSummary($customerId);
        if (!$summary->getSummaryId()) {
            $summary->setWebsiteId($websiteId);
            $summary->setCustomerId($customerId);
            $defaultBalanceUpdateNotification = $this->config->isSubscribeCustomersToNotificationsByDefault($websiteId)
                ? SubscribeStatus::SUBSCRIBED
                : SubscribeStatus::NOT_SUBSCRIBED;
            $summary->setBalanceUpdateNotificationStatus($defaultBalanceUpdateNotification);
        }

        if ($isTransaction) {
            $transactionBalance = $data->getBalance();
            $balance = ($summary->getBalance() + $transactionBalance) >= 0
                ? ($summary->getBalance() + $transactionBalance)
                : 0;
            $summary->setBalance($balance);

            if ($transactionBalance > 0) {
                $transactionTypes = [TransactionType::ORDER_CANCELED, TransactionType::REIMBURSE_OF_SPENT_STORE_CREDIT];
                if (in_array($data->getType(), $transactionTypes)) {
                    $summary->setSpend(
                        $summary->getSpend() - $transactionBalance
                    );
                } else {
                    $summary->setEarn(
                        $summary->getEarn() + $transactionBalance
                    );
                }
            } else {
                $summary->setSpend(
                    $summary->getSpend() + abs($transactionBalance)
                );
            }
        } else {
            if ($data->getBalanceUpdateNotificationStatus() !== null) {
                $summary->setBalanceUpdateNotificationStatus($data->getBalanceUpdateNotificationStatus());
            }
        }

        return $summary;
    }

    /**
     * Save summary
     *
     * @param SummaryInterface $summary
     * @throws CouldNotSaveException
     * @return boolean
     */
    private function saveSummary(SummaryInterface $summary)
    {
        $result = false;
        try {
            $result = $this->storeCreditSummaryRepository->save($summary);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $result;
    }
}
