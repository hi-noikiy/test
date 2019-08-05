<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Api\Data;

interface TransactionInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const TRANSACTION_ID = 'transaction_id';
    const AFFILIATE_ACCOUNT_ID = 'affiliate_account_id';
    const PROGRAM_ID = 'program_id';
    const ORDER_INCREMENT_ID = 'order_increment_id';
    const PROFIT = 'profit';
    const BALANCE = 'balance';
    const COMMISSION = 'commission';
    const DISCOUNT = 'discount';
    const UPDATED_AT = 'updated_at';
    const TYPE = 'type';
    const STATUS = 'status';
    /**#@-*/

    /**
     * @return int
     */
    public function getTransactionId();

    /**
     * @param int $transactionId
     *
     * @return \Amasty\Affiliate\Api\Data\TransactionInterface
     */
    public function setTransactionId($transactionId);

    /**
     * @return int
     */
    public function getAffiliateAccountId();

    /**
     * @param int $affiliateAccountId
     *
     * @return \Amasty\Affiliate\Api\Data\TransactionInterface
     */
    public function setAffiliateAccountId($affiliateAccountId);

    /**
     * @return int
     */
    public function getProgramId();

    /**
     * @param int $programId
     *
     * @return \Amasty\Affiliate\Api\Data\TransactionInterface
     */
    public function setProgramId($programId);

    /**
     * @return string|null
     */
    public function getOrderIncrementId();

    /**
     * @param string|null $orderIncrementId
     *
     * @return \Amasty\Affiliate\Api\Data\TransactionInterface
     */
    public function setOrderIncrementId($orderIncrementId);

    /**
     * @return float|null
     */
    public function getProfit();

    /**
     * @param float|null $profit
     *
     * @return \Amasty\Affiliate\Api\Data\TransactionInterface
     */
    public function setProfit($profit);

    /**
     * @return float|null
     */
    public function getBalance();

    /**
     * @param float|null $balance
     *
     * @return \Amasty\Affiliate\Api\Data\TransactionInterface
     */
    public function setBalance($balance);

    /**
     * @return float|null
     */
    public function getCommission();

    /**
     * @param float|null $commission
     *
     * @return \Amasty\Affiliate\Api\Data\TransactionInterface
     */
    public function setCommission($commission);

    /**
     * @return float|null
     */
    public function getDiscount();

    /**
     * @param float|null $discount
     *
     * @return \Amasty\Affiliate\Api\Data\TransactionInterface
     */
    public function setDiscount($discount);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     *
     * @return \Amasty\Affiliate\Api\Data\TransactionInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     *
     * @return \Amasty\Affiliate\Api\Data\TransactionInterface
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $status
     *
     * @return \Amasty\Affiliate\Api\Data\TransactionInterface
     */
    public function setStatus($status);
}
