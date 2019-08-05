<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Controller\Adminhtml\Banner;

class NewAction extends \Amasty\Affiliate\Controller\Adminhtml\Banner
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
