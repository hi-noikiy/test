<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Model\ResourceModel\Coupon;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\Affiliate\Model\Coupon', 'Amasty\Affiliate\Model\ResourceModel\Coupon');
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()
            ->joinLeft(
                ['salesrule_coupon' => $this->getTable('salesrule_coupon')],
                'main_table.coupon_id = salesrule_coupon.coupon_id',
                ['code']
            )
            ->joinLeft(
                ['amasty_affiliate_program' => $this->getTable('amasty_affiliate_program')],
                'main_table.program_id = amasty_affiliate_program.program_id',
                ['name']
            );

        return $this;
    }

    /**
     * @param $programId
     * @param $accountId
     * @return $this
     */
    public function addFilterForCoupon($programId, $accountId)
    {
        $this
            ->addFieldToFilter('main_table.program_id', ['eq' => $programId])
            ->addFieldToFilter('main_table.account_id', ['eq' => $accountId]);

        return $this;
    }

    /**
     * @param $coupon
     * @return $this
     */
    public function addCouponFilter($coupon)
    {
        $this->addFieldToFilter('code', ['eq' => $coupon]);

        return $this;
    }

    public function isAffiliateCoupon($coupon)
    {
        $isAffiliateCoupon = false;

        $this->addCouponFilter($coupon);
        if ($this->getSize() > 0) {
            $isAffiliateCoupon = true;
        }

        return $isAffiliateCoupon;
    }

    /**
     * @param int $isActive
     * @return $this
     */
    public function addProgramActiveFilter($isActive = 1)
    {
        $this->addFieldToFilter('amasty_affiliate_program.is_active', ['eq' => $isActive]);

        return $this;
    }

    /**
     * @param $accountId
     * @return $this
     */
    public function addAccountIdFilter($accountId)
    {
        $this->addFieldToFilter('account_id', ['eq' => $accountId]);

        return $this;
    }
}
