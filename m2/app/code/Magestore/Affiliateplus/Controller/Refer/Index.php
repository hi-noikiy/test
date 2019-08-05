<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Controller\Refer;

/**
 * Action Index
 */
class Index extends \Magestore\Affiliateplus\Controller\AbstractAction
{
    /**
     * Execute action
     */
    public function execute()
    {
        if (!$this->_dataHelper->isAffiliateModuleEnabled()) {
            return $this->_redirect($this->getBaseUrl());
        }
        if (!$this->_dataHelper->getConfig('affiliateplus/refer/enable')) {
            return $this->_redirect('affiliateplus/index/index');
        }
        if ($this->_accountHelper->accountNotLogin()) {
            return $this->_redirect('affiliateplus/account/login');
        }
        if ($this->_accountHelper->isNotAvailableAccount()){
            return $this->_redirect('affiliateplus/index/index');
        }

        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('Refer Friends and Earn Money'));

        return $resultPage;
    }
}
