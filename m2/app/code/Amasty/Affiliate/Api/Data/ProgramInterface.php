<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Api\Data;

interface ProgramInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const PROGRAM_ID = 'program_id';
    const RULE_ID = 'rule_id';
    const NAME = 'name';
    const WITHDRAWAL_TYPE = 'withdrawal_type';
    const IS_ACTIVE = 'is_active';
    const COMMISSION_VALUE = 'commission_value';
    const COMMISSION_PER_PROFIT_AMOUNT = 'commission_per_profit_amount';
    const COMMISSION_VALUE_TYPE = 'commission_value_type';
    const FROM_SECOND_ORDER = 'from_second_order';
    const COMMISSION_TYPE_SECOND = 'commission_type_second';
    const COMMISSION_VALUE_SECOND = 'commission_value_second';
    const IS_LIFETIME = 'is_lifetime';
    const FREQUENCY = 'frequency';
    const TOTAL_SALES = 'total_sales';
    /**#@-*/

    /**
     * @return int
     */
    public function getProgramId();

    /**
     * @param int $programId
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setProgramId($programId);

    /**
     * @return int|null
     */
    public function getRuleId();

    /**
     * @param int|null $ruleId
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setRuleId($ruleId);

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string|null $name
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getWithdrawalType();

    /**
     * @param string $withdrawalType
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setWithdrawalType($withdrawalType);

    /**
     * @return int
     */
    public function getIsActive();

    /**
     * @param int $isActive
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setIsActive($isActive);

    /**
     * @return float|null
     */
    public function getCommissionValue();

    /**
     * @param float|null $commissionValue
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setCommissionValue($commissionValue);

    /**
     * @return float|null
     */
    public function getCommissionPerProfitAmount();

    /**
     * @param float|null $commissionPerProfitAmount
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setCommissionPerProfitAmount($commissionPerProfitAmount);

    /**
     * @return string|null
     */
    public function getCommissionValueType();

    /**
     * @param string|null $commissionValueType
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setCommissionValueType($commissionValueType);

    /**
     * @return int
     */
    public function getFromSecondOrder();

    /**
     * @param int $fromSecondOrder
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setFromSecondOrder($fromSecondOrder);

    /**
     * @return string|null
     */
    public function getCommissionTypeSecond();

    /**
     * @param string|null $commissionTypeSecond
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setCommissionTypeSecond($commissionTypeSecond);

    /**
     * @return float|null
     */
    public function getCommissionValueSecond();

    /**
     * @param float|null $commissionValueSecond
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setCommissionValueSecond($commissionValueSecond);

    /**
     * @return int
     */
    public function getIsLifetime();

    /**
     * @param int $isLifetime
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setIsLifetime($isLifetime);

    /**
     * @return string|null
     */
    public function getFrequency();

    /**
     * @param string|null $frequency
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setFrequency($frequency);

    /**
     * @return float|null
     */
    public function getTotalSales();

    /**
     * @param float|null $totalSales
     *
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface
     */
    public function setTotalSales($totalSales);
}
