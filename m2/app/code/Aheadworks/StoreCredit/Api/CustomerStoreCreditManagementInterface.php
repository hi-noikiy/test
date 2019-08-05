<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Api;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;

/**
 * @api
 */
interface CustomerStoreCreditManagementInterface
{
    /**
     * Spend customer store credit on checkout
     *
     * @param  int $customerId
     * @param  int $spendStoreCredit
     * @param  OrderInterface $order
     * @param  int $websiteId
     * @return boolean
     */
    public function spendStoreCreditOnCheckout($customerId, $spendStoreCredit, $order, $websiteId);

    /**
     * Refund to store credit
     *
     * @param  int $customerId
     * @param  int $refundToStoreCredit
     * @param  int $orderId
     * @param  CreditmemoInterface $creditmemo
     * @param  int $websiteId
     * @return boolean
     */
    public function refundToStoreCredit($customerId, $refundToStoreCredit, $orderId, $creditmemo, $websiteId);

    /**
     * Reimbursed spent store credit
     *
     * @param  int $customerId
     * @param  int $refundToStoreCredit
     * @param  int $orderId
     * @param  CreditmemoInterface $creditmemo
     * @param  int $websiteId
     * @return boolean
     */
    public function reimbursedSpentStoreCredit($customerId, $refundToStoreCredit, $orderId, $creditmemo, $websiteId);

    /**
     * Reimbursed spent store credit on order cancel
     *
     * @param  int $customerId
     * @param  int $refundToStoreCredit
     * @param  OrderInterface $order
     * @param  int $websiteId
     * @return boolean
     */
    public function reimbursedSpentStoreCreditOrderCancel($customerId, $refundToStoreCredit, $order, $websiteId);

    /**
     * Save transaction created by admin
     *
     * @param  [] $transactionData
     * @return boolean
     */
    public function saveAdminTransaction($transactionData);

    /**
     * Calculate spend store credit
     *
     * @param int $customerId
     * @param float $amount
     * @return float
     */
    public function calculateSpendStoreCredit($customerId, $amount);

    /**
     * Retrieve customer balance
     *
     * @param  int $customerId
     * @return float
     */
    public function getCustomerStoreCreditBalance($customerId);

    /**
     * Retrieve customer balance update notification status
     *
     * @param  int $customerId
     * @return int
     */
    public function getCustomerBalanceUpdateNotificationStatus($customerId);

    /**
     * Retrieve customer balance сurrency
     *
     * @param  int $customerId
     * @return float
     */
    public function getCustomerStoreCreditBalanceCurrency($customerId);

    /**
     * Retrieve customer store credit details
     *
     * @param int $customerId
     * @return \Aheadworks\StoreCredit\Api\Data\CustomerStoreCreditDetailsInterface
     */
    public function getCustomerStoreCreditDetails($customerId);

    /**
     * Reset customer
     *
     * @return void
     */
    public function resetCustomer();
}
