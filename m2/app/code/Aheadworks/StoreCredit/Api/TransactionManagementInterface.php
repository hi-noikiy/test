<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Api;

use Aheadworks\StoreCredit\Api\Data\TransactionInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Aheadworks\StoreCredit\Model\Source\NotifiedStatus;

/**
 * @api
 */
interface TransactionManagementInterface
{
    /**
     * Create empty transaction instance
     *
     * @return TransactionInterface
     */
    public function createEmptyTransaction();

    /**
     * Create transaction
     *
     * @param CustomerInterface $customer
     * @param int $balance
     * @param int $type
     * @param string $commentToCustomer
     * @param string $commentToCustomerPlaceholder
     * @param string $commentToAdmin
     * @param int $websiteId
     * @param int $balanceUpdateNotified
     * @param array $arguments
     * @param int $adminUserId
     * @return boolean
     */
    public function createTransaction(
        CustomerInterface $customer,
        $balance,
        $type,
        $commentToCustomer = null,
        $commentToCustomerPlaceholder = null,
        $commentToAdmin = null,
        $websiteId = null,
        $balanceUpdateNotified = NotifiedStatus::NOT_SUBSCRIBED,
        $arguments = [],
        $adminUserId = null
    );

    /**
     * Save transaction
     *
     * @param TransactionInterface $transaction
     * @return boolean|TransactionInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function saveTransaction(TransactionInterface $transaction);

    /**
     * Update notified status
     *
     * @param int $transactionId
     * @param int $balanceUpdateNotified
     * @return boolean|TransactionInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function updateNotifiedStatus($transactionId, $balanceUpdateNotified);

    /**
     * Update current balance
     *
     * @param int $transactionId
     * @param float $balance
     * @return boolean|TransactionInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function updateCurrentBalance($transactionId, $balance);
}
