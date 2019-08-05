<?php
/**
 * Copyright © 2016 Collinsharper. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CollinsHarper\Moneris\Model\ResourceModel\RecurringPaymentQueue;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use CollinsHarper\Moneris\Model\RecurringPaymentQueue as Model;
use CollinsHarper\Moneris\Model\ResourceModel\RecurringPaymentQueue as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * Specify model and resource model for collection
     */
    protected function _construct()
    {
        $this->_init(
            Model::class,
            ResourceModel::class
        );
    }
}