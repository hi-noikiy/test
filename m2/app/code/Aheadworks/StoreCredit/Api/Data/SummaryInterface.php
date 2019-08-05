<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Api\Data;

/**
 * @api
 */
interface SummaryInterface
{
    /**
     * #@+
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case
     */
    const SUMMARY_ID = 'summary_id';
    const WEBSITE_ID = 'website_id';
    const CUSTOMER_ID = 'customer_id';
    const BALANCE = 'balance';
    const EARN = 'earn';
    const SPEND = 'spend';
    const BALANCE_UPDATE_NOTIFICATION_STATUS = 'balance_update_notification_status';
    /**#@-*/

    /**
     * Set summary Id
     *
     * @param int $summaryId
     * @return SummaryInterface
     */
    public function setSummaryId($summaryId);

    /**
     * Get summary Id
     *
     * @return int
     */
    public function getSummaryId();

    /**
     * Set website Id
     *
     * @param int $websiteId
     * @return SummaryInterface
     */
    public function setWebsiteId($websiteId);

    /**
     * Get website Id
     *
     * @return int
     */
    public function getWebsiteId();

    /**
     * Set customer id
     *
     * @param int $customerId
     * @return SummaryInterface
     */
    public function setCustomerId($customerId);

    /**
     * Get customer id
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Set summary balance
     *
     * @param float $balance
     * @return SummaryInterface
     */
    public function setBalance($balance);

    /**
     * Get summary balance
     *
     * @return float
     */
    public function getBalance();

    /**
     * Set summary earn
     *
     * @param float $earn
     * @return SummaryInterface
     */
    public function setEarn($earn);

    /**
     * Get summary earn
     *
     * @return float
     */
    public function getEarn();

    /**
     * Set summary spend
     *
     * @param float $spend
     * @return SummaryInterface
     */
    public function setSpend($spend);

    /**
     * Get summary spend
     *
     * @return float
     */
    public function getSpend();

    /**
     * Set summary balance update notification status
     *
     * @param int $balanceUpdateNotificationStatus
     * @return SummaryInterface
     */
    public function setBalanceUpdateNotificationStatus($balanceUpdateNotificationStatus);

    /**
     * Get summary balance update notification status
     *
     * @return int
     */
    public function getBalanceUpdateNotificationStatus();
}
