<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model;

use Aheadworks\StoreCredit\Api\Data\SummaryInterface;
use Aheadworks\StoreCredit\Model\ResourceModel\Summary as SummaryResource;

/**
 * Class Aheadworks\StoreCredit\Model\Summary
 */
class Summary extends \Magento\Framework\Model\AbstractModel implements SummaryInterface
{
    /**
     *  {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(SummaryResource::class);
    }

    /**
     *  {@inheritDoc}
     */
    public function setSummaryId($summaryId)
    {
        return $this->setData(self::SUMMARY_ID, $summaryId);
    }

    /**
     *  {@inheritDoc}
     */
    public function getSummaryId()
    {
        return $this->getData(self::SUMMARY_ID);
    }

    /**
     *  {@inheritDoc}
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     *  {@inheritDoc}
     */
    public function getWebsiteId()
    {
        return $this->getData(self::WEBSITE_ID);
    }

    /**
     *  {@inheritDoc}
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     *  {@inheritDoc}
     */
    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    /**
     *  {@inheritDoc}
     */
    public function setBalance($balance)
    {
        return $this->setData(self::BALANCE, $balance);
    }

    /**
     *  {@inheritDoc}
     */
    public function getBalance()
    {
        return $this->getData(self::BALANCE);
    }

    /**
     *  {@inheritDoc}
     */
    public function setEarn($earn)
    {
        return $this->setData(self::EARN, $earn);
    }

    /**
     *  {@inheritDoc}
     */
    public function getEarn()
    {
        return $this->getData(self::EARN);
    }

    /**
     *  {@inheritDoc}
     */
    public function setSpend($spend)
    {
        return $this->setData(self::SPEND, $spend);
    }

    /**
     *  {@inheritDoc}
     */
    public function getSpend()
    {
        return $this->getData(self::SPEND);
    }

    /**
     *  {@inheritDoc}
     */
    public function setBalanceUpdateNotificationStatus($balanceUpdateNotificationStatus)
    {
        return $this->setData(self::BALANCE_UPDATE_NOTIFICATION_STATUS, $balanceUpdateNotificationStatus);
    }

    /**
     *  {@inheritDoc}
     */
    public function getBalanceUpdateNotificationStatus()
    {
        return $this->getData(self::BALANCE_UPDATE_NOTIFICATION_STATUS);
    }
}
