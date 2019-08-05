<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model\Filters;

use Aheadworks\StoreCredit\Model\Filters\Transaction\CustomerSelection;
use Magento\Framework\Filter\AbstractFactory;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

/**
 * Class Aheadworks\StoreCredit\Model\Filters\Factory
 */
class Factory extends AbstractFactory
{
    /**
     * @var array
     */
    protected $invokableClasses = [
        'date' => Date::class,
        'aw_storecredit_custselect' => CustomerSelection::class,
    ];
}
