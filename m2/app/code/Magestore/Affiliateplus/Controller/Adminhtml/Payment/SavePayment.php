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
class SavePayment extends \Magestore\Affiliateplus\Controller\Adminhtml\Affiliateplus
{
    /**
     * @return \Magestore\Affiliateplus\Model\PaymentFactory
     */
    public function getModelPayment(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment');
    }
    /**
     * Execute action
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        if (!$this->getRequest()->getParam('masspayout')) {
            return $resultForward->forward('index');
        }
        $paymentId = $this->getRequest()->getParam('payment_id');
        $payment = $this->getModelPayment()->load($paymentId)
            ->addData($this->getRequest()->getPostValue());
        $helper = $payment->getPayment()->getPaymentHelper();
        if (!$helper) {
            return $resultForward->forward('index');
        }
        $account = $this->_accountFactory->create();
        $storeId = $this->getRequest()->getParam('store');
        if ($storeId && $this->_helper->getConfig('affiliateplus/account/balance', $storeId) != 'global') {
            $account->setStoreViewId($storeId);
        }
        $account->load($payment->getData('account_id'));
        if (!$account->getId()) {
            return $resultForward->forward('index');
        }
        $resPayment = $helper->payoutByApi($account, $payment->getData('amount'), $storeId, $payment->getId());
        if ($resPayment && $resPayment->getId()) {
            if ($resPayment->getStatus() == 3) {
                $this->messageManager->addSuccess(__('This payout has been transfered successfully!'));
            } else {
                if ($resPayment->getErrorMessage()) {
                    $this->messageManager->addError($resPayment->getErrorMessage());
                    $resPayment->setErrorMessage(null);
                    $this->messageManager->addNotice(__('This payout has not been transfered!'));
                } else {
                    $this->messageManager->addError(__('This payout has not been transfered!'));
                }
            }
            return $resultRedirect->setPath('affiliateplusadmin/payment/edit', ['payment_id' => $resPayment->getId()]);
        }
        $this->messageManager->addError(__('Your payout was not transfered and saved!'));
        return $resultForward->forward('index');
    }
}