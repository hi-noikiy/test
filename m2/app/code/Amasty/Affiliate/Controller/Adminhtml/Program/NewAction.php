<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Controller\Adminhtml\Program;

class NewAction extends \Amasty\Affiliate\Controller\Adminhtml\Program
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
