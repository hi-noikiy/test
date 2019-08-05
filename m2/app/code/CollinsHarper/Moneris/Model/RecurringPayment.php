<?php
/**
 * Copyright © 2016 Collinsharper. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CollinsHarper\Moneris\Model;

use CollinsHarper\Moneris\Model\ResourceModel\RecurringPayment as ResourceModel;
use Magento\Framework\Model\AbstractModel;

class RecurringPayment extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}