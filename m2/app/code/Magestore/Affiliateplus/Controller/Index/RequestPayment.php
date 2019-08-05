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
namespace Magestore\Affiliateplus\Controller\Index;

/**
 * Action Index
 */
class RequestPayment extends \Magestore\Affiliateplus\Controller\AbstractAction
{
    /**
     * Execute action
     */
    public function execute()
    {
        if (!$this->_dataHelper->isAffiliateModuleEnabled()) {
            return $this->_redirect($this->getBaseUrl());
        }
        if ($this->_accountHelper->accountNotLogin()) {
            return $this->_redirect('affiliateplus/account/login');
        }
        if ($this->_accountHelper->isNotAvailableAccount()){
            return $this->_redirect('affiliateplus/index/index');
        }
        if ($this->_accountHelper->disableWithdrawal()) {
            $this->messageManager->addError(__('Request withdrawal not allowed at this time.'));
            if (!$this->_accountHelper->disableStoreCredit()) {
                return $this->_redirect('affiliateplus/index/payments');
            }
            return $this->_redirect('affiliateplus/index/listTransaction');
        }

        $paymentCodes = $this->_objectManager->get('Magestore\Affiliateplus\Helper\Payment')->getAvailablePaymentCode();
        if (!count($paymentCodes)) {
            $this->messageManager->addError(__('There is no payment method on file for your account. Please update your details or contact us to solve the problem.'));
            return $this->_redirect('affiliateplus/index/payments');
        } elseif (count($paymentCodes) == 1) {
            $paymentCode = $this->getRequest()->getParam('payment_method');
            if (!$paymentCode) {
                $paymentCode = current($paymentCodes);
            }

        } else {
            $paymentCode = $this->getRequest()->getParam('payment_method');
        }
        if (!$paymentCode) {
            $this->messageManager->addNotice(__('Please chose an available payment method!'));
            return $this->_redirect('affiliateplus/index/paymentForm', $this->getRequest()->getPostValue());
        }
        if (!in_array($paymentCode, $paymentCodes)) {
            $this->messageManager->addError(__('This payment method is not available, please choose an alternative payment method.'));
            return $this->_redirect('affiliateplus/index/paymentForm', $this->getRequest()->getPostValue());
        }
        $account = $this->_accountHelper->getAccount();
        $store = $this->_storeManager->getStore();
        $amount = $this->getRequest()->getParam('amount');
        $amount = $amount / $this->getAbtracTemplate()->convertPrice(1);
//        if ($amount < $this->getConfigHelper()->getPaymentConfig('payment_release')) {
//            $this->messageManager->addNotice(__('The minimum balance required to request withdrawal is %1'
//                , $this->getAbtracTemplate()->formatPrice($this->getConfigHelper()->getPaymentConfig('payment_release'))));
//            return $this->_redirect('affiliateplus/index/paymentForm');
//        }
        $amountInclTax = $this->getRequest()->getParam('amount_incl_tax');
        $accountBalance = $this->getAbtracTemplate()->convertPrice($account->getBalance());
        if ($amountInclTax) {
            if ($amountInclTax > $amount && $amountInclTax > $accountBalance) {
                $this->messageManager->addError(__('The withdrawal requested cannot exceed your current balance (%1).'
                    , $this->getAbtracTemplate()->formatPrice($account->getBalance())));
                return $this->_redirect('affiliateplus/index/paymentForm');
            }
        }
        if ($amount > $accountBalance) {
            $this->messageManager->addError(__('The withdrawal requested cannot exceed your current balance (%1).'
                , $this->getAbtracTemplate()->formatPrice($account->getBalance())));

            return $this->_redirect('affiliateplus/index/paymentForm');
        }
        $now = new \DateTime();
        $payment = $this->getAffiliateModel()
            ->setPaymentMethod($paymentCode)
            ->setAmount($amount)
            ->setAccountId($account->getId())
            ->setAccountName($account->getName())
            ->setAccountEmail($account->getEmail())
            ->setRequestTime($now)
            ->setStatus(1)
            ->setIsRequest(1)
            ->setIsPayerFee(0);
        if ($this->getConfigHelper()->getPaymentConfig('who_pay_fees') == 'payer' && $paymentCode == 'paypal') {
            $payment->setIsPayerFee(1);
        }
        if ($payment->hasWaitingPayment()) {
            $this->messageManager->addError(__('You are having a pending request!'));
            return $this->_redirect('affiliateplus/index/payments');
        }
        if ($this->getConfigHelper()->getSharingConfig('balance') == 'store') {
            $payment->setStoreIds($store->getId());
        }
        $paymentMethod = $payment->getPayment();
        $paymentObj = new \Magento\Framework\DataObject(
            [
            'payment_code' => $paymentCode,
            'required' => true,
            ]
        );
        $this->getEventManager()->dispatch("affiliateplus_request_payment_action_$paymentCode",
            [
                'payment_obj' => $paymentObj,
                'payment' => $payment,
                'payment_method' => $paymentMethod,
                'request' => $this->getRequest(),
             ]
        );
        $paymentCode = $paymentObj->getPaymentCode();
        if ($paymentCode == 'paypal') {
            $paypalEmail = $this->getRequest()->getParam('paypal_email');
            if ($paypalEmail && $paypalEmail != $account->getPaypalEmail()) {
                $accountModel = $this->getModelAccount()
                    ->setStoreViewId($store->getId())
                    ->load($account->getId());
                try {
                    $accountModel->setPaypalEmail($paypalEmail)
                        ->setId($account->getId())
                        ->save();
                } catch (\Exception $e) {

                }
            }

            $paypalEmail = $paypalEmail ? $paypalEmail : $account->getPaypalEmail();
            if ($paypalEmail) {
                $paymentMethod->setEmail($paypalEmail);
                $paymentObj->setRequired(false);
            }
        }
        if ($paymentCode != 'bank') {
            if ($paymentObj->getRequired()) {
                $this->messageManager->addError(__('Please fill out all required fields in the form below.'));
                return $this->_redirect('affiliateplus/index/paymentForm', $this->getRequest()->getPostValue());
            }
        }

        try {
            $payment->save();
            $paymentMethod->savePaymentMethodInfo();
            $payment->sendMailRequestPaymentToSales();
            $this->messageManager->addSuccess(__('Your request has been sent to admin for approval.'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $this->_redirect('affiliateplus/index/payments');

    }

    /**
     * @return \Magestore\Affiliateplus\Model\Payment
     */
    public function getAffiliateModel()
    {
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment');
    }

}
