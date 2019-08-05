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
namespace Magestore\Affiliateplus\Controller\Adminhtml\Payment;

use Magento\Framework\Controller\ResultFactory;

/**
 * Action Edit
 */
class Edit extends \Magestore\Affiliateplus\Controller\Adminhtml\Affiliateplus
{
    /**
     * Execute action
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('payment_id');
        $storeId = $this->getRequest()->getParam('store');
        $payment = $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment');
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($id) {
            $payment->setStoreId($storeId)
                        ->load($id);
            if (!$payment->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        }

        if($payment->getId() || $id==0) {
            $data = $this->_getSession()->getFormData(true);
            if(!$id){    // add info from account
                $accountId = $this->getRequest()->getParam('account_id');
                $account = $this->_objectManager->create('Magestore\Affiliateplus\Model\Account');
                if ($storeId = $this->getRequest()->getParam('store')) {
                    if ($this->_helper->getConfig('affiliateplus/account/balance', $storeId) == 'store') {
                        $account->setStoreId($storeId);
                    }
                }
                $account->load($accountId);
                $data['affiliateplus_account'] = $account;
                $data['account_name'] = $account->getName();
                $data['account_email'] = $account->getEmail();
                $data['account_id'] = $account->getId();
                $data['paypal_email'] = $account->getPaypalEmail();
                $data['moneybooker_email'] = $account->getMoneybookerEmail();
                $data['account_balance'] = $account->getBalance();
                if ($this->getRequest()->getParam('method') == 'api')
                    $data['payment_method'] = 'paypal';
            }
            if (!empty($data)) {
                $payment->addData($data);
            }

            if ($payment->getId())
                $payment->addPaymentInfo();

            $this->_objectManager->get('Magento\Framework\Registry')->register('payment_data', $payment);
        } else {
            $this->messageManager->addError(__('This item does not exist.'));
            return $resultRedirect->setPath('*/*/');
        }
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        if($id){
            $resultPage->getConfig()->getTitle()->prepend(__('Edit Payment'));
        }else {
            $resultPage->getConfig()->getTitle()->prepend(__('Add New Payment'));
        }
        $resultPage->setActiveMenu('Magestore_Affiliateplus::magestoreaffiliateplus');
        return $resultPage;
    }
}