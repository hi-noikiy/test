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
class EditPost extends \Magestore\Affiliateplus\Controller\AbstractAction
{
    /**
     * get account model
     *
     * @return Magestore\Affiliateplus\Model\Account
     */
    protected function _getAffiliateAccount()
    {
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Account');
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
     * Get address data
     * @param $customer
     * @param $data
     * @return mixed
     */
    protected function _getCustomerAddressData($customer, $data) {
        if (isset($data['account_address_id']) && $data['account_address_id']) {
            $address = $this->getCustomerAddress()->load($data['account_address_id']);
        } else {
            $address_data = $this->getRequest()->getPost('account');
            $address = $this->getCustomerAddress()
                ->setData($address_data)
                ->setParentId($customer->getId())
                ->setFirstname($customer->getFirstname())
                ->setLastname($customer->getLastname())
                ->setId(null);
            $customer->addAddress($address);
        }
        return $address;
    }

    /**
     * Change password
     * @param $customer
     * @return \Magento\Framework\Phrase
     */
    protected function _changePassword($customer){
        $currPass = $this->getRequest()->getPost('current_password');
        $newPass = $this->getRequest()->getPost('password');
        $confPass = $this->getRequest()->getPost('confirmation');
        $error = 0;

        $oldPass = $this->getSession()->getCustomer()->getPasswordHash();
        if (strpos($oldPass, ':')){
            list($_salt, $salt) = explode(':', $oldPass);
        }else{
            $salt = false;
        }
        if ($customer->hashPassword($currPass, $salt) == $oldPass) {
            if (strlen($newPass)) {
                $customer->setPassword($newPass);
                $customer->setConfirmation($confPass);
            } else {
                $error = 1;
            }
        } else {
            $error = 2;
        }
        return $error;
    }

    /**
     * Execute action
     */
    public function execute()
    {
        if (!$this->_dataHelper->isAffiliateModuleEnabled()) {
            return $this->_redirectUrl($this->getBaseUrl());
        }
        if ($this->_accountHelper->accountNotLogin()){
            return $this->_redirect('affiliateplus/account/login');
        }
        if (!$this->getRequest()->isPost()){
            return $this->_redirect('affiliateplus/account/edit');
        }
        $session = $this->getSession();

        $customerSession = $this->getCustomerSession();
        $inputFilter = new \Zend_Filter_Input(
            ['dob' => $this->_dateFilter],
            [],
            $this->getRequest()->getPostValue()
        );
        $data = $inputFilter->getUnescaped();
        $customer = $customerSession->getCustomer();
        $customer->addData($data);
        $customer->setFirstname($data['firstname']);
        $customer->setLastname($data['lastname']);
        $errors = array();

        $address = $this->_getCustomerAddressData($customer, $data);
        $errors = $address->validate();

        if (!is_array($errors)){
            $errors = array();
        }

        if ($this->getRequest()->getParam('change_password')) {
            $error = $this->_changePassword($customer);
            if($error == 1){
                $errors[] = __('The New Password field is empty. Please enter a new password.');
            } elseif($error == 2){
                $errors[] = __('Please re-enter your current password.');
            }
        }

        try {
            $validationCustomer = $customer->validate();

            if (is_array($validationCustomer)){
                $errors = array_merge($validationCustomer, $errors);
            }
            $validationResult = (count($errors) == 0);

            if (true === $validationResult) {
                $customer->save();
                if (!$address->getId()){
                    $address->save();
                }
            }else {
                foreach ($errors as $error){
                    $this->messageManager->addError($error);
                }
                $formData = $this->getRequest()->getPost();
                $formData['account_name'] = $customer->getName();
                $address_id = (isset($formData['account_address_id']) && $formData['account_address_id']) ? $formData['account_address_id'] : '';
                foreach($formData as $key => $item){
                    if($key == 'account'){
                        $item['address_id'] = $address_id;
                    }
                }
                $session->setAffiliateFormData($formData);
                return $this->_redirect('affiliateplus/account/edit');
            }
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $formData = $this->getRequest()->getPost();
            $formData['account_name'] = $customer->getName();
            $formData['account']['address_id'] = isset($formData['account_address_id']) ? $formData['account_address_id'] : '';
            $session->setAffiliateFormData($formData);
            return $this->_redirect('affiliateplus/account/edit');
        }

        $account = $this->_getAffiliateAccount()
            ->setStoreId($this->_storeManager->getStore()->getId())
            ->load($session->getAccount()->getId());
        try {
            $account->setData('referring_website', $data['referring_website']);

            $data['paypal_email'] = isset($data['paypal_email']) ? $data['paypal_email'] : '';
            $account->setData('name', $customer->getName())
                ->setData('paypal_email', $data['paypal_email'])
                ->setData('notification', isset($data['notification']) ? 1 : 0);
            if ($address)
                $account->setData('address_id', $address->getId());
            $account->save();
            $successMessage = __('Your account information has been saved.');
            $this->messageManager->addSuccess($successMessage);
            return $this->_redirect('affiliateplus/account/edit');
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $formData = $this->getRequest()->getPost();
            $formData['account_name'] = $customer->getName();
            $formData['account']['address_id'] = isset($formData['account_address_id']) ? $formData['account_address_id'] : '';
            $session->setAffiliateFormData($formData);
            return $this->_redirect('affiliateplus/account/edit');
        }
    }
}
