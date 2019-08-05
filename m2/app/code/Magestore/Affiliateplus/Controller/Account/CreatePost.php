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
 * Class CreatePost
 * @package Magestore\Affiliateplus\Controller\Account
 */
class CreatePost extends \Magestore\Affiliateplus\Controller\AbstractAction
{


    /**
     * get customer model
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getModelCustomer()
    {
        return $this->_objectManager->create('Magento\Customer\Model\Customer');
    }

    /**
     * get account model
     *
     * @return Magestore\Affiliateplus\Model\Account
     */
    public function getAffiliateAccount()
    {
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Account');
    }

    /**
     * Get config helper
     *
     * @return Magestore\Affiliateplus\Helper\Config
     */
    public function getConfigHelper()
    {
        return $this->_objectManager->get('Magestore\Affiliateplus\Helper\Config');
    }

    /**
     * get Customer Quote
     *
     * @return Magento\Customer\Model\Quote
     */
    public function getCustomerAddress()
    {
        return $this->_objectManager->create('Magento\Customer\Model\Address');
    }
    /**
     * Execute action
     */
    public function execute()
    {
        if (!$this->_dataHelper->isAffiliateModuleEnabled()) {
            return $this->_redirect($this->getBaseUrl());
        }
        if (!$this->getRequest()->isPost())
            return $this->_redirect('affiliateplus/account/register');

        $session = $this->getSession();
        $coreSession = $this->getCoreSession();
        $customerSession = $this->getCustomerSession();

        $address = '';

        $referredBy = $this->getRequest()->getPost('referred_by', '');
        if (($referredBy) && ($referredBy != '')) {
            $websiteId = $this->_storeManager->getWebsite()->getId();
            $customerId = $this->getModelCustomer()->setWebsiteId($websiteId)->loadByEmail($referredBy)->getId();
            if ($customerId && ($customerId != '')) {
                $account = $this->getAffiliateAccount()->getCollection()->addFieldToFilter('customer_id', $customerId)->getFirstItem();
                if ($account && ($account->getAccountId())) {
                    $accountCode = $account->getIdentifyCode();
                    $expiredTime = $this->getConfigHelper()->getGeneralConfig('expired_time');
                    $this->getSession()->setData('top_affiliate_indentify_code', $accountCode);
                    $this->_cookieHelper->saveCookie($accountCode, $expiredTime, true);
                }
            }
        }

        if ($session->isRegistered()) {
            return $this->_redirect('affiliateplus/account/login');
        } elseif ($customerSession->isLoggedIn()) {
            $inputFilter = new \Zend_Filter_Input(
                ['dob' => $this->_dateFilter],
                [],
                $this->getRequest()->getPostValue()
            );
            $data = $inputFilter->getUnescaped();
//            $data = $this->_filterDates($this->getRequest()->getPost(), array('dob'));
            //Check Captcha Code
            $captchaCode = $coreSession->getData('register_account_captcha_code');
            if ($captchaCode != $data['account_captcha']) {
                $session->setAffiliateFormData($this->getRequest()->getPost());
                $this->messageManager->addError(__('The verification code entered is incorrect. Please try again.'));
                return $this->_redirect('affiliateplus/account/register');
            }
            //Customer not register affiliate account
            $customer = $customerSession->getCustomer();
            if (isset($data['account_address_id']) && $data['account_address_id']) {
                $address = $this->getCustomerAddress()->load($data['account_address_id']);
            } elseif ($this->getConfigHelper()->getSharingConfig('required_address')) {
                $address_data = $this->getRequest()->getPost('account');
                $address = $this->getCustomerAddress()
                    ->setData($address_data)
                    ->setParentId($customer->getId())
                    ->setFirstname($customer->getFirstname())
                    ->setLastname($customer->getLastname())
                    ->setId(null);
                $customer->addAddress($address);
                $errors = $address->validate();
                if (!is_array($errors))
                    $errors = [];
                try {
                    $validationCustomer = $customer->validate();
                    if (is_array($validationCustomer))
                        $errors = array_merge($validationCustomer, $errors);
                    $validationResult = (count($errors) == 0);
                    if (true === $validationResult) {
                        $customer->save();
                        $address->save();
                    } else {
                        foreach ($errors as $error)
                            $this->messageManager->addError($error);
                        $formData = $this->getRequest()->getPost();
                        $formData['account_name'] = $customer->getName();
                        $session->setAffiliateFormData($formData);
                        return $this->_redirect('affiliateplus/account/register');
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                    $formData = $this->getRequest()->getPost();
                    $formData['account_name'] = $customer->getName();
                    $session->setAffiliateFormData($formData);
                    return $this->_redirect('affiliateplus/account/register');
                }
            }
        } else {
            $inputFilter = new \Zend_Filter_Input(
                ['dob' => $this->_dateFilter],
                [],
                $this->getRequest()->getPostValue()
            );
            $data = $inputFilter->getUnescaped();

            //Check Captcha Code
            $captchaCode = $coreSession->getData('register_account_captcha_code');
            if ($captchaCode != $data['account_captcha']) {
                $session->setAffiliateFormData($this->getRequest()->getPost());
                $this->messageManager->addError(__('The verification code entered is incorrect. Please try again.'));
                return $this->_redirect('affiliateplus/account/register');
            }

            //Create new customer and affiliate account
            $customerSession->setEscapeMessages(true);
            $errors = [];
            if (!$customer = $this->_coreRegistry->registry('current_customer')) {
                $customer = $this->getModelCustomer()->setId(null);
            }
            $customerAccount = $this->_fieldsetConfig->getFieldset('customer_account');
            foreach ($customerAccount as $code => $node)
                if (isset($node['create']) && isset($data[$code])) {
                    if ($code == 'email')
                        $data[$code] = trim($data[$code]);
                    $customer->setData($code, $data[$code]);
                }

            $customer->getGroupId();

            if ($this->getConfigHelper()->getSharingConfig('required_address')) {
                $address_data = $this->getRequest()->getPost('account');
                $address = $this->getCustomerAddress()
                    ->setData($address_data)
                    ->setFirstname($customer->getFirstname())
                    ->setLastname($customer->getLastname())
                    ->setIsDefaultBilling(true)
                    ->setIsDefaultShipping(true)
                    ->setId(null);
                $customer->addAddress($address);

                $errors = $address->validate();
            }
            if (!is_array($errors))
                $errors = [];

            try {
                $customer->setPasswordConfirmation($data['confirmation']);
                $validationCustomer = $customer->validate();
                if (is_array($validationCustomer))
                    $errors = array_merge($validationCustomer, $errors);
                $validationResult = (count($errors) == 0);
                if (true === $validationResult) {
                    $customer->save();
                    if ($address)
                        $address->save();
                    if ($customer->isConfirmationRequired()) {
                        $customer->sendNewAccountEmail(
                            'confirmation', $customerSession->getBeforeAuthUrl(), $this->_storeManager->getStore()->getId()
                        );
                    } else {
                        $customerSession->setCustomerAsLoggedIn($customer);
                    }
                } else {
                    foreach ($errors as $error)

                        $this->messageManager->addError($error);
                    $formData = $this->getRequest()->getPost();
                    $formData['account_name'] = $customer->getName();
                    $session->setAffiliateFormData($formData);
                    return $this->_redirect('affiliateplus/account/register');
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $formData = $this->getRequest()->getPost();
                $formData['account_name'] = $customer->getName();
                $session->setAffiliateFormData($formData);
                return $this->_redirect('affiliateplus/account/register');
            }
        }

        try {

            $successMessage = '';
            $successMessage = $this->_accountHelper->createAffiliateAccount($address, $this->getRequest()->getPost('paypal_email'), $customer, $this->getRequest()->getPost('notification'), $this->getRequest()->getPost('referring_website'), $successMessage, $referredBy, $coreSession);

            $this->messageManager->addSuccess($successMessage);

            if($this->getSession()->setData('top_affiliate_indentify_code')) {
                $this->getSession()->setData('top_affiliate_indentify_code', '');
            }

            return $this->_redirect('affiliateplus/index/index');
        } catch (\Exception $e) {
            $coreSession->addError($e->getMessage());
            $formData = $this->getRequest()->getPost();
            $formData['account_name'] = $customer->getName();
            $session->setAffiliateFormData($formData);
            return $this->_redirect('affiliateplus/account/register');
        }
    }
}
