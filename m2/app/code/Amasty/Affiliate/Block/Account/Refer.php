<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Block\Account;

class Refer extends \Amasty\Affiliate\Block\Account\Social
{
    /**
     * @var string
     */
    protected $_template = 'account/refer.phtml';

    public function getText()
    {
        return $this->_scopeConfig->getValue('amasty_affiliate/friends/text');
    }
}
