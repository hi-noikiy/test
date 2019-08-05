<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Block\Customer\StoreCreditBalance;

use Aheadworks\StoreCredit\Block\Customer\StoreCreditBalance;

/**
 * Class Aheadworks\StoreCredit\Block\Customer\StoreCreditBalance\Toplink
 */
class Toplink extends StoreCreditBalance
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_StoreCredit::customer/toplinks/balance.phtml';

    /**
     * Is ajax request or not
     *
     * @return bool
     */
    public function isAjax()
    {
        return $this->_request->isAjax();
    }

    /**
     * Checking customer login status
     *
     * @return bool
     */
    public function customerLoggedIn()
    {
        return (bool)$this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * Can show block
     *
     * @return bool
     */
    public function canShow()
    {
        if ($this->config->isStoreCreditBalanceTopLinkAtFrontend()
            && (!$this->config->isHideIfStoreCreditBalanceEmpty()
                || ($this->config->isHideIfStoreCreditBalanceEmpty() &&
                    (float)$this->getCustomerStoreCreditBalance() > 0))
        ) {
            return true;
        }
        return false;
    }
}
