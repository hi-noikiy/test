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
 * Class Register
 * @package Magestore\Affiliateplus\Controller\Account
 */
class Register extends \Magestore\Affiliateplus\Controller\AbstractAction
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
        if ($isRegiter==true) {
            if ($isLogin==true) {
                $this->messageManager->addSuccess(__('You have logged in successfully.'));
                return $this->_redirect('affiliateplus/index/index');
            } else {
                return $this->_redirect('affiliateplus/account/login');
            }
            if ($this->_accountHelper->isNotAvailableAccount()){
                return $this->_redirect('affiliateplus/index/index');
            }
        }
        if ($this->_sessionCustomer->isLoggedIn()) {
            $formData = ['account_name' => $this->_sessionCustomer->getCustomer()->getName()];
            $this->_getSession->setAffiliateFormData($formData);
        }
        $resultPage = $this->_pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Sign up for an Affiliate Account'));
        return $resultPage;

    }





}
