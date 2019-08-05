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
 * Class LoginPost
 * @package Magestore\Affiliateplus\Controller\Account
 */
class LoginPost extends \Magestore\Affiliateplus\Controller\AbstractAction
{
    /**
     * Execute action
     */
    public function execute()
    {
        if (!$this->_dataHelper->isAffiliateModuleEnabled()) {
            return $this->_redirect($this->getBaseUrl());
        }

        if (!$this->getRequest()->isPost() || $this->getCustomerSession()->isLoggedIn())
            return $this->_redirect('affiliateplus/account/login');
        $login = $this->getRequest()->getPost('login');
        if (!empty($login['username']) && !empty($login['password'])) {
            try {
                $loginPost = $this->_objectManager->create('Magento\Customer\Api\AccountManagementInterface');
                $customer = $loginPost->authenticate(
                    $login['username'],
                    $login['password']
                );
                $this->getCustomerSession()->setCustomerDataAsLoggedIn($customer);
                if ($this->getSession()->getDirectUrl()) {
                    $this->_redirect($this->getSession()->getDirectUrl());
                    $this->getSession()->setDirectUrl(null);
                    return;
                }
                return $this->_redirect('affiliateplus/index/index');
            } catch (\EmailNotConfirmedException $e) {
                $value = $this->_customerUrl->getEmailConfirmationUrl($login['username']);
                $message = __(
                    'This account is not confirmed.' .
                    ' <a href="%1">Click here</a> to resend confirmation email.',
                    $value
                );
                $this->messageManager->addError($message);
                $this->getCoreSession()->setLoginFormData(['email' => $login['username']]);
            } catch (\AuthenticationException $e) {
                $message = __('Invalid login or password.');
                $this->messageManager->addError($message);
                $this->getCoreSession()->setLoginFormData(['email' => $login['username']]);
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Invalid login or password.'));
            }
        } else {
            $this->messageManager->addError($this->__('Please enter your username and password.'));
        }

        return $this->_redirect('affiliateplus/account/login');

    }





}
