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
class ConfirmRequest extends \Magestore\Affiliateplus\Controller\AbstractAction
{
    /**
     * Payment model
     *
     * @var \Magestore\Affiliateplus\Model\Payment
     */
    protected  $_paymentModel;
    /**
     * Execute action
     */
    public function execute()
    {
        if (!$this->_dataHelper->isAffiliateModuleEnabled()) {
            return $this->_redirect($this->getBaseUrl());
        }
        $session = $this->getSession();
        $account = $session->getAccount();
        $params = $this->getRequest()->getPostValue();

        if (!count($params)) {
            $params = $this->getRequest()->getParams();
        }
        if ($this->getPaymentModel()->setAccountId($account->getId())->hasWaitingPayment()) {
            $this->messageManager->addError(__('You are having a pending request!'));
            return $this->_redirect('affiliateplus/index/payments');
        }
        if ($this->accountNotLogin()) {
            return $this->_redirect('affiliateplus/account/login');
        }
        if ($this->_accountHelper->isNotAvailableAccount()){
            return $this->_redirect('affiliateplus/index/index');
        }
        if (!isset($params['payment_method'])) {
            $storeId = $this->getStoreManager()->getStore()->getId();
            $paymentMethods = $this->getPaymentHelper()->getAvailablePayment($storeId);

            if (count($paymentMethods) == 1) {
                foreach ($paymentMethods as $code => $value) {
                    $params['payment_method'] = $code;
                }
            } else {
                $params['payment_method'] = 'paypal';
            }

            if ($params['payment_method'] == 'paypal') {
                if (isset($params['paypal_email']) && $params['paypal_email']) {
                    $params['email'] = $params['paypal_email'];
                } else {
                    $params['email'] = $account->getPaypalEmail();
                }

            } else if ($params['payment_method'] == 'moneybooker') {

                if (isset($params['moneybooker_email']) && $params['moneybooker_email']) {
                    $params['email'] = $params['moneybooker_email'];
                } else {
                    $params['email'] = $account->getMoneybookerEmail();
                }

            }
        } else {
            if ($params['payment_method'] == 'paypal') {
                if (isset($params['paypal_email']) && $params['paypal_email']) {
                    $params['email'] = $params['paypal_email'];
                } else {
                    $params['email'] = $account->getPaypalEmail();
                }

            } else if ($params['payment_method'] == 'moneybooker') {
                if (isset($params['moneybooker_email']) && $params['moneybooker_email']) {
                    $params['email'] = $params['moneybooker_email'];
                } else {
                    $params['email'] = $account->getMoneybookerEmail();
                }

            } else if ($params['payment_method'] == 'bank') {
                // check select bank account avaiable.
                if (isset($params['payment_bankaccount_id']) && $params['payment_bankaccount_id']) {
                    // selected bank account
                    $model = $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment\Bankaccount')->load($params['payment_bankaccount_id']);
                    if ($model->getData()) {
                        $params['bank'] = $model->getData();
                    }
                }
            }
        }
        /** check email verify */
        if (isset($params['payment_method']) && $params['payment_method']) {
            $require = $this->getPaymentHelper()->isRequireAuthentication($params['payment_method']);
            if ($require) {
                if (isset($params['email']) && $params['email']) {
                    $verify = $this->getModelPaymentVerify()->loadExist($account->getId(), $params['email'], $params['payment_method']);
                    if (!$verify->isVerified()) {
                        $this->messageManager->addError(__('The email is not authenticated. Please verify authentication code.'));
                        return $this->_redirect('affiliateplus/index/paymentForm');
                    }
                }
            }
        }


        /** end */
        $paramObject = new \Magento\Framework\DataObject(
            [
                'params' => $params
            ]
        );
        $this->getEventManager()->dispatch('affiliateplus_payment_prepare_data', [
            'payment_data' => $paramObject,
            'file' => $this->getRequest()->getFiles(),
        ]
    );

        $params = $paramObject->getParams();
        $payment = $this->getModelPayment();
        $payment->setData($params);
        $this->_objectManager->get('Magento\Framework\Registry')->register('confirm_payment_data', $payment);

        $session->setPaymentMethod($payment->getPaymentMethod());
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Confirm'));
        return $resultPage;

    }

    /**
     * @return mixed
     */
    public function getPaymentModel(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment');
    }

}
