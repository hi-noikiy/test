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
 * Action NewAction
 */
class Save extends \Magestore\Affiliateplus\Controller\Adminhtml\Affiliateplus
{
    /**
     * Execute action
     */
    public function execute()
    {
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if($data = $this->getRequest()->getPostValue()){
            if (empty($data['payment_method'])){
                $data['payment_method'] = 'paypal';
            }
            if (empty($data['status'])){
                $data['status'] = 1;
            }
            $paymentId = $this->getRequest()->getParam('payment_id');
            $payment = $this->_getPaymentModel()->load($paymentId)
                ->setPaymentMethod($data['payment_method']);

            $storeId = $this->getRequest()->getParam('store');
            if (!$storeId) {
                $stores = $this->_storeManager->getStores();
                foreach ($stores as $store) {
                    $storeIds[] = $store->getId();
                }
            }else{
                $storeIds = [$storeId];
            }
            if (!$paymentId){
                $payment->setStoreIds(implode(',', $storeIds));
            }

            $payment->setData($data)
                    ->setId($paymentId);
            $whoPayFees = $this->_helperConfig->getPaymentConfig('who_pay_fees');
            if (!$paymentId) {
                if ($whoPayFees == 'payer' && $payment->getPaymentMethod() == 'paypal'){
                    $payment->setIsPayerFee(1);
                }else{
                    $payment->setIsPayerFee(0);
                }
                $payment->setIsRequest(0);
            }

            $account = $this->_accountFactory->create()
                ->setStoreId($storeId)
                ->load($payment->getAccountId());

            $amount = $this->getRequest()->getParam('amount');
//            if (!$paymentId && $amount < $this->_helperConfig->getPaymentConfig('payment_release')) {
//                $this->messageManager->addError(__('The minimum balance allowing withdrawal request is %1', $this->formatPrice($this->_helperConfig->getPaymentConfig('payment_release'))));
//                $this->_session->setFormData($data);
//                return $resultRedirect->setPath('*/*/new', ['account_id' => $account->getId()]);
//            }
            if (!$paymentId && $amount > $account->getBalance()) {
                $this->messageManager->addError(__('The withdrawal amount cannot exceed the account balance: %1.',$this->formatPrice($this->_helperConfig->getPaymentConfig('payment_release'))));
                $this->_session->setFormData($data);
                return $resultRedirect->setPath('*/*/new', ['account_id' => $account->getId()]);
            }
            if($paymentId && $whoPayFees == 'recipient' && ($account->getBalance() - $this->getRequest()->getParam('fee') < 0)){
                $this->messageManager->addError(__('You cannot process the payment because the withdrawal amount and fee (%1) is greater than available balance (%2).',$this->formatPrice($amount + $this->getRequest()->getParam('fee')), $this->formatPrice($account->getBalance())));
                $this->_session->setFormData($data);
                return $resultRedirect->setPath('*/*/new', ['account_id' => $account->getId(), 'payment_id'=>$paymentId]);
            }

            try{
                if ($payment->getRequestTime() == NULL){
                    $now = new \DateTime();
                    $payment->setRequestTime($now);
                }

                $payment->setData('affiliateplus_account', $account);
                if (!$payment->getId() && !$payment->getStatus()) {
                    $payment->setStatus('1');
                }

                $payment->save();
                if(isset($data['paypal_email']) && $data['paypal_email'] != '') {
                    $dataEmail = $data['paypal_email'];
                    $paypalPayment = $payment->getPayment()->addData($data)
                        ->setEmail($dataEmail)
                        ->savePaymentMethodInfo();
                    if (isset($data['transaction_id']) && $data['transaction_id']) {
                        $paypalPayment->setTransactionId($data['transaction_id']);
                    }
                }
                $this->messageManager->addSuccess(__('Your withdrawal request has been saved.'));
                $this->_session->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['payment_id' => $payment->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            }catch(\Exception $e){

                $this->messageManager->addError($e->getMessage());
                $this->_session->setFormData($data);
                return $resultRedirect->setPath('*/*/edit', ['payment_id' => $payment->getId()]);
            }
        }
        $this->messageManager->addError(__('Unable to find payment to save'));
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @return \Magestore\Affiliateplus\Model\Payment
     */
    protected function _getPaymentModel(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment');
    }
}