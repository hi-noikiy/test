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
namespace Magestore\Affiliateplus\Controller\Account;

/**
 * Class Login
 * @package Magestore\Affiliateplus\Controller\Account
 */
class Login extends \Magestore\Affiliateplus\Controller\AbstractAction
{
    /**
     * Execute action
     */
    public function execute()
    {
        if (!$this->_dataHelper->isAffiliateModuleEnabled()) {
            return $this->_redirect($this->getBaseUrl());
        }
        $isLogin =  $this->_accountHelper->isLoggedIn();
        $isRegiter = $this->_accountHelper->isRegistered();
        if ($isLogin==true) {
            $this->messageManager->addSuccess(__('You have logged in successfully.'));
            return $this->_redirect('affiliateplus/index/index');
        } elseif ($isRegiter==true) {
            $this->messageManager->addError(__('Your affiliate account is currently disabled. Please contact us to resolve this issue.'));
            return $this->_redirect('affiliateplus/index/index');
        }
        if ($this->getRequest()->getServer('HTTP_REFERER')){
            $this->_getSession->setDirectUrl($this->getRequest()->getServer('HTTP_REFERER'));
        }
        $resultPage = $this->_pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Affiliate login'));
        return $resultPage;

    }





}
