<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model;

use Aheadworks\StoreCredit\Api\Data\CustomerStoreCreditDetailsInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Aheadworks\StoreCredit\Model\CustomerStoreCreditDetails
 */
class CustomerStoreCreditDetails extends AbstractModel implements CustomerStoreCreditDetailsInterface
{
    /**
     *  {@inheritDoc}
     */
    public function getCustomerStoreCreditBalance()
    {
        return $this->getData(self::CUSTOMER_STORE_CREDIT_BALANCE);
    }

    /**
     *  {@inheritDoc}
     */
    public function setCustomerStoreCreditBalance($balance)
    {
        return $this->setData(self::CUSTOMER_STORE_CREDIT_BALANCE, $balance);
    }

    /**
     *  {@inheritDoc}
     */
    public function getCustomerStoreCreditBalanceCurrency()
    {
        return $this->getData(self::CUSTOMER_STORE_CREDIT_BALANCE_CURRENCY);
    }

    /**
     *  {@inheritDoc}
     */
    public function setCustomerStoreCreditBalanceCurrency($balance)
    {
        return $this->setData(self::CUSTOMER_STORE_CREDIT_BALANCE_CURRENCY, $balance);
    }

    /**
     *  {@inheritDoc}
     */
    public function getCustomerBalanceUpdateNotificationStatus()
    {
        return $this->getData(self::CUSTOMER_BALANCE_UPDATE_NOTIFICATION_STATUS);
    }

    /**
     *  {@inheritDoc}
     */
    public function setCustomerBalanceUpdateNotificationStatus($balanceUpdateNotificationStatus)
    {
        return $this->setData(self::CUSTOMER_BALANCE_UPDATE_NOTIFICATION_STATUS, $balanceUpdateNotificationStatus);
    }
}
