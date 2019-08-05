<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_SecurityAuth
 */


namespace Amasty\SecurityAuth\Controller\Adminhtml;

abstract class Auth extends \Magento\Backend\App\Action
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_SecurityAuth::securityauth');
    }
}
