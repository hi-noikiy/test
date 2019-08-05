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
class Edit extends \Magestore\Affiliateplus\Controller\AbstractAction
{
    /**
     * Execute action
     */
    public function execute()
    {
        if (!$this->_dataHelper->isAffiliateModuleEnabled()) {
            return $this->_redirectUrl($this->getBaseUrl());
        }
        if(!$this->_accountHelper->isLoggedIn()){
            $this->messageManager->addNotice(__('You have to logged in before editing information'));
            return $this->_redirect('affiliateplus/account/login');
        }
        if ($this->_accountHelper->isNotAvailableAccount()){
            return $this->_redirect('affiliateplus/index/index');
        }
        $session = $this->_affiliateSession;
        $customer = $session->getCustomer();
        $account = $session->getAccount();
        $formData = $customer->getData();
        $formData['account'] = $session->getAccount()->getData();
        $formData['account_name'] = $customer->getName();
        $formData['paypal_email'] = $account->getPaypalEmail();
        $formData['notification'] = $account->getNotification();


        /*
          hainh update for adding referring website to form data in order to use on edit form
          22-04-2014
         */
        $formData['referring_website'] = $account->getReferringWebsite();
        $session->setAffiliateFormData($formData);
        $resultPage = $this->_pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Account Settings'));
        return $resultPage;
    }
}
