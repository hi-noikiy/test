<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Model\ResourceModel\Withdrawal;

use Amasty\Affiliate\Model\ResourceModel\Transaction;

class Collection extends Transaction\Collection
{
    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->addFieldToFilter('type', ['eq' => \Amasty\Affiliate\Model\Transaction::TYPE_WITHDRAWAL]);

        return $this;
    }

    public function addAccountIdFilter($accountId)
    {
        $this->addFieldToFilter('affiliate_account_id', ['eq' => $accountId]);

        return $this;
    }

    public function getCurrentAccountPendingAmount()
    {
        $this->addFieldToFilter(
            'main_table.affiliate_account_id',
            ['eq' => $this->accountRepository->getCurrentAccount()->getAccountId()]
        );
        $this->addFieldToFilter('main_table.status', ['eq' => \Amasty\Affiliate\Model\Withdrawal::STATUS_PENDING]);
        $this->getSelect()->columns(['pending' => 'SUM(commission)']);
        $pending = $this->getFirstItem()->getPending();

        return $pending;
    }
}
