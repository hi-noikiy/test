<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class TransactionType
 *
 * @package Aheadworks\StoreCredit\Model\Source
 */
class TransactionType implements ArrayInterface
{
    /**#@+
     * Balance update action values
     */
    const BALANCE_ADJUSTED_BY_ADMIN = 1;
    const ORDER_CANCELED = 2;
    const REFUND_BY_STORE_CREDIT = 3;
    const REIMBURSE_OF_SPENT_STORE_CREDIT = 4;
    const STORE_CREDIT_USED_IN_ORDER = 5;
    /**#@-*/

    /**
     *  {@inheritDoc}
     */
    public function toOptionArray()
    {
        return $this->getBalanceUpdateActions();
    }

    /**
     *  {@inheritDoc}
     */
    public function getBalanceUpdateActions()
    {
        return [
            [
                'value' => self::BALANCE_ADJUSTED_BY_ADMIN,
                'label' => __('Balance adjusted by admin')
            ],
            [
                'value' => self::ORDER_CANCELED,
                'label' => __('Order canceled')
            ],
            [
                'value' => self::REFUND_BY_STORE_CREDIT,
                'label' => __('Refund by Store Credit')
            ],
            [
                'value' => self::REIMBURSE_OF_SPENT_STORE_CREDIT,
                'label' => __('Reimburse of spent Store Credit')
            ],
            [
                'value' => self::STORE_CREDIT_USED_IN_ORDER,
                'label' => __('Store Credit used in order')
            ]
        ];
    }
}
