<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Controller\Adminhtml\Account;

class NewAction extends \Amasty\Affiliate\Controller\Adminhtml\Account
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
