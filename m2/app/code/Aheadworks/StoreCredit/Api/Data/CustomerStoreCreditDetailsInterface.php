<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Api\Data;

/**
 * @api
 */
interface CustomerStoreCreditDetailsInterface
{
    /**#@+
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case
     */
    const CUSTOMER_STORE_CREDIT_BALANCE = 'customer_store_credit_balance';
    const CUSTOMER_STORE_CREDIT_BALANCE_CURRENCY = 'customer_store_credit_balance_currency';
    const CUSTOMER_BALANCE_UPDATE_NOTIFICATION_STATUS = 'customer_balance_update_notification_status';
    /**#@-*/

    /**
     * Retrieve customer Store Credit balance
     *
     * @return float
     */
    public function getCustomerStoreCreditBalance();

    /**
     * Set customer Store Credit balance
     *
     * @param float $balance
     * @return CustomerStoreCreditDetailsInterface
     */
    public function setCustomerStoreCreditBalance($balance);

    /**
     * Retrieve customer Store Credit balance currency
     *
     * @return float
     */
    public function getCustomerStoreCreditBalanceCurrency();

    /**
     * Set customer Store Credit balance currency
     *
     * @param float $balance
     * @return CustomerStoreCreditDetailsInterface
     */
    public function setCustomerStoreCreditBalanceCurrency($balance);

    /**
     * Retrieve customer balance update notification status
     *
     * @return int
     */
    public function getCustomerBalanceUpdateNotificationStatus();

    /**
     * Set customer balance update notification status
     *
     * @param int $balanceUpdateNotificationStatus
     * @return CustomerStoreCreditDetailsInterface
     */
    public function setCustomerBalanceUpdateNotificationStatus($balanceUpdateNotificationStatus);
}
