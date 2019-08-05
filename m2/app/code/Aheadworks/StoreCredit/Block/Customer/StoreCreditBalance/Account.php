<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Block\Customer\StoreCreditBalance;

use Aheadworks\StoreCredit\Block\Customer\StoreCreditBalance;

/**
 * Class Aheadworks\StoreCredit\Block\Customer\StoreCreditBalance\Account
 */
class Account extends StoreCreditBalance
{
    /**
     * Retrieve customer transaction grid
     *
     * @return string
     */
    public function getTransactionHtml()
    {
        return $this->getChildHtml('aw_sc_transaction');
    }
}
