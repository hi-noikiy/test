<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class BalanceUpdateActions
 *
 * @package Aheadworks\StoreCredit\Model\Source
 */
class BalanceUpdateActions implements ArrayInterface
{
    /**
     * @var TransactionType
     */
    private $transactionType;

    /**
     * @param TransactionType $transactionType
     */
    public function __construct(TransactionType $transactionType)
    {
        $this->transactionType = $transactionType;
    }

    /**
     *  {@inheritDoc}
     */
    public function toOptionArray()
    {
        return $this->transactionType->getBalanceUpdateActions();
    }
}
