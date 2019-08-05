<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model\ResourceModel\Summary;

use Aheadworks\StoreCredit\Model\Summary;
use Aheadworks\StoreCredit\Model\ResourceModel\Summary as SummaryResource;

/**
 * Class Aheadworks\StoreCredit\Model\ResourceModel\Summary\Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(Summary::class, SummaryResource::class);
    }
}
