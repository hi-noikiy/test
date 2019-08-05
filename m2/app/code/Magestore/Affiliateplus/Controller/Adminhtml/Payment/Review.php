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

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Controller\ResultFactory;
/**
 * Action Edit
 */
class Review extends \Magestore\Affiliateplus\Controller\Adminhtml\Affiliateplus
{
    /**
     * Execute action
     */
    public function execute()
    {
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $storeId = $this->getRequest()->getParam('store');
            $amount = $this->getRequest()->getParam('amount');
            $paymentId = $this->getRequest()->getParam('payment_id');
            $account = $this->_accountFactory->create()
                ->setStoreViewId($storeId)
                ->load($this->getRequest()->getParam('account_id'));
            if (!$paymentId && $amount < $this->_helperConfig->getPaymentConfig('payment_release', $storeId)) {
                $this->messageManager->addError(__('The minimum balance allowing withdrawal request is %1', $this->formatPrice($this->_helperConfig->getPaymentConfig('payment_release', $storeId), true, false)));
                $this->getBackendSession()->setFormData($data);
                $this->_redirect('*/*/new', ['account_id' => $this->getRequest()->getParam('account_id')]);
                return;
            }
            if (!$paymentId && $amount > $account->getBalance()) {
                $this->messageManager->addError(__('The withdrawal amount cannot exceed the account balance: %1.', $this->formatPrice($account->getBalance())));
                $this->getBackendSession()->setFormData($data);
                $this->_redirect('*/*/new', ['account_id' => $this->getRequest()->getParam('account_id')]);
                return;
            }
            $whoPayFees = $this->_helperConfig->getPaymentConfig('who_pay_fees');
            if($paymentId && $whoPayFees == 'recipient' && ($account->getBalance() - $this->getRequest()->getParam('fee') < 0)){
                $this->messageManager->addError(__('You cannot process the payment because the withdrawal amount and fee (%1) is greater than available balance (%2).',$this->formatPrice($amount + $this->getRequest()->getParam('fee')), $this->formatPrice($account->getBalance())));
                $this->_session->setFormData($data);
                return $resultRedirect->setPath('*/*/new', ['account_id' => $account->getId(), 'payment_id'=>$paymentId]);
            }
            if ($this->getRequest()->getParam('masspayout')) {
                $this->messageManager->addNotice(__('The system will transfer money to your affiliate account immediately through the paygate.'));
            }

            $resultPage = $this->_resultPageFactory->create();
            $resultPage->addContent(
                $resultPage->getLayout()->createBlock('Magestore\Affiliateplus\Block\Adminhtml\Payment\Review\Edit')
            );
            $resultPage->setActiveMenu('Magestore_Affiliateplus::managewithdrawals');
            return $resultPage;
        } else {
            $this->messageManager->addError(__('Unable to find payment to review'));
        }
        $this->_redirect('*/*/');
    }

    /**
     * @return \Magento\Backend\Model\Session
     */
    public function getBackendSession()
    {
        return $this->_objectManager->create('Magento\Backend\Model\Session');
    }
    /**
     * @param $value
     * @return mixed
     */
    public function formatPrice($value)
    {
        return $this->_priceCurrency->format(
            $value,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getStore()
        );
    }
}