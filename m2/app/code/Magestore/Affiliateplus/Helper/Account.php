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
namespace Magestore\Affiliateplus\Helper;

/**
 * Class Account
 * @package Magestore\Affiliateplus\Helper
 */
class Account extends HelperAbstract
{
    /**
     * @return mixed
     */
    public function getBalanceLabel() {
        return __('Balance: %1', $this->getAccountBalanceFormated());
    }

    /**
     * @return mixed
     */
    public function getAccountBalanceFormated() {
        $scope = $this->getConfig('affiliateplus/account/balance');
        if($scope == 'website'){
            return $this->convertCurrency($this->getAccount()->getWebsiteBalance());
        }
        else{
            return $this->convertCurrency($this->getAccount()->getBalance());
        }
    }

    /**
     * @return mixed
     */
    public function getAccount() {
        return $this->getAffiliateSession()->getAccount();
    }

    /**
     * @return bool
     */
    public function accountNotLogin() {
        return !$this->isLoggedIn();
    }

    public function isNotAvailableAccount(){
        if($this->accountNotLogin()){
            return true;
        } else {
            $account = $this->getAccount();
            if($account && $account->getId() && ($account->getApproved() == \Magestore\Affiliateplus\Model\Status::STATUS_DISABLED || $account->getStatus() == \Magestore\Affiliateplus\Model\Status::STATUS_DISABLED)){
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * check account logged in
     *
     * @return mixed
     */
    public function isLoggedIn() {
        return $this->getAffiliateSession()->isLoggedIn();
    }

    /**
     * @return bool
     */
    public function customerNotLogin() {
        return !$this->customerLoggedIn();
    }

    /**
     * check
     *
     * @return mixed
     */
    public function customerLoggedIn() {
        return $this->_objectManager->create('Magento\Customer\Model\Session')->isLoggedIn();
    }

    /**
     * @return bool
     */
    public function accountNotRegistered() {
        return !$this->isRegistered();
    }

    /**
     * check customer is registered
     *
     * @return mixed
     */
    public function isRegistered() {
        return $this->getAffiliateSession()->isRegistered();
    }

    /**
     * @param $code
     * @param null $store
     * @return mixed
     */
    public function getMaterialConfig($code, $store = null){
        return $this->getConfig('affiliateplus/general/material_'.$code,$store);
    }

    /**
     * @return bool
     */
    public function disableMaterials(){
        return ($this->isNotAvailableAccount() || !$this->getMaterialConfig('enable'));
    }

    /**
     * @return bool
     */
    public function hideWithdrawalMenu() {
        return $this->disableStoreCredit() && $this->disableWithdrawal();
    }

    /**
     * @return string
     */
    public function getWithdrawalLabel() {
        if ($this->disableWithdrawal()) {
            return 'Store Credits';
        }
        return 'Withdrawals';
    }

    /**
     * @return bool
     */
    public function disableStoreCredit() {
        if ($this->isNotAvailableAccount()) {
            return true;
        }
        if ($this->getPaymentConfig('store_credit')) {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     */
    public function disableWithdrawal() {
        if ($this->isNotAvailableAccount()) {
            return true;
        }
        if ($this->getPaymentConfig('withdrawals')) {
            return false;
        }
        return true;
    }

    /**
     * @param $code
     * @param null $store
     * @return mixed
     */
    public function getPaymentConfig($code, $store = null){
        return $this->getConfig('affiliateplus/payment/'.$code,$store);
    }

    /**
     * @param $address
     * @param $paypalEmail
     * @param $customer
     * @param $notification
     * @param $referringWebsite
     * @param $successMessage
     * @param null $referredBy
     * @param null $coreSession
     * @return string
     */
    public function createAffiliateAccount($address, $paypalEmail, $customer, $notification, $referringWebsite, $successMessage, $referredBy=null, $coreSession=null) {
        $now = new \DateTime();
        $account = $this->_accountFactory->create()
            ->setData('customer_id', $customer->getId())
            ->setData('name', $customer->getName())
            ->setData('email', $customer->getEmail())
            ->setData('paypal_email', $paypalEmail)
            ->setData('created_time', $now)
            ->setData('balance', 0)
            ->setData('total_commission_received', 0)
            ->setData('total_paid', 0)
            ->setData('total_clicks', 0)
            ->setData('unique_clicks', 0)
            ->setData('status', 1)
            ->setData('status_default', 1)
            ->setData('approved_default', 1)
            ->setData('notification', $notification)
            ->setData('referring_website', $referringWebsite)
            ->setData('referred_by', $referredBy)
        ;
        $successMessage = $this->_objectManager->get('Magestore\Affiliateplus\Helper\Config')->getSharingConfig('notification_after_signing_up');
        $coreSession = $coreSession ? $coreSession : $this->_sessionManagerInterface;
        if ($this->_objectManager->get('Magestore\Affiliateplus\Helper\Config')->getSharingConfig('need_approved')) {
            $account->setData('status', 2);
            $account->setData('approved', 2);
            $coreSession->setData('has_been_signup', true);
            $successMessage .= ' ' . __('Thank you for signing up for our Affiliate program. Your registration will be reviewed and we\'ll inform you as soon as possible.');
        }
        if ($address)
            $account->setData('address_id', $address->getId());
        $account->setData('identify_code', $account->generateIdentifyCode());
        $account->setStoreId($this->_storeManager->getStore()->getId())->save();

//        send email
        $account->sendMailToNewAccount($account->getIdentifyCode());
        $account->sendNewAccountEmailToAdmin();
        return $successMessage;
    }

    /**
     * @return bool
     */
    public function isEnoughBalance() {
        return ($this->getAccount()->getBalance() >= $this->getPaymentConfig('payment_release'));
    }

    /**
     * @param $websiteId
     * @return mixed
     */
    public function getStoreIdsByWebsite($websiteId){
        if(is_null($websiteId))
            $websiteId = $this->_storeManager->getWebsite()->getId();
        $stores = $this->_objectManager->create('Magento\Store\Model\Store')->getCollection()
            ->addFieldToFilter('website_id', $websiteId)
            ->addFieldToFilter('is_active', 1)
            ->getAllIds();
        return $stores;
    }
}
