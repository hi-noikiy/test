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
class PaymentForm extends \Magestore\Affiliateplus\Controller\AbstractAction
{
    /**
     * Execute action
     */
    public function execute()
    {
        if (!$this->_dataHelper->isAffiliateModuleEnabled()) {
            return $this->_redirect($this->getBaseUrl());
        }

        if ($this->getRequest()->getParam('from_request_page')) {
            $account = $this->getSession()->getAccount();
            if ($this->_objectManager->create('Magestore\Affiliateplus\Model\Payment')->setAccountId($account->getId())->hasWaitingPayment()) {
                $this->messageManager->addError(__('You are having a pending request!'));
                return $this->_redirect('affiliateplus/index/payments');
            }
        }

//        $amount = $this->getSession()->getPaymentAmount();
//        $payment = $this->getSession()->getPayment();
//        if ($amount) {
//            $payment->setData('amount', $amount);
//        }

        if ($this->_accountHelper->accountNotLogin()) {
            return $this->_redirect('affiliateplus/account/login');
        }
        if ($this->_accountHelper->isNotAvailableAccount()){
            return $this->_redirect('affiliateplus/index/index');
        }

        if ($this->_accountHelper->disableWithdrawal()) {
            if (!$this->_accountHelper->disableStoreCredit()) {
                return $this->_redirect('affiliateplus/index/payments');
            }
            return $this->_redirect('affiliateplus/index/listTransaction');
        }

        if (!$this->_accountHelper->isEnoughBalance()) {
            $baseCurrency = $this->_storeManager->getStore()->getBaseCurrency();
            $this->messageManager->addNotice(__('The minimum balance required to request withdrawal is %1'
                , $baseCurrency->format($this->_objectManager->get('Magestore\Affiliateplus\Helper\Config')->getPaymentConfig('payment_release'), [], false)));
            return $this->_redirect('affiliateplus/index/listTransaction');
        }
        $resultPage = $this->_pageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Request Payment'));
        return $resultPage;

    }


}
