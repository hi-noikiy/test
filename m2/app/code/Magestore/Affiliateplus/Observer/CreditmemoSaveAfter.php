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
namespace Magestore\Affiliateplus\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magestore\Affiliateplus\Model\Transaction;

/**
 * Class ProductGetFinalPrice
 * @package Magestore\Affiliateplus\Observer
 */
class CreditmemoSaveAfter extends AbtractObserver implements ObserverInterface
{
    /**
     * Reduce affiliate's balance
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->_helper->isAffiliateModuleEnabled()){
            return $this;
        }
        $creditMemo = $observer['creditmemo'];
        $request = $this->_request;
        if ($creditMemo->getState() != \Magento\Sales\Model\Order\Creditmemo::STATE_REFUNDED) {
            return $this;
        }
        // Refund for Affiliate Credit
        $this->creditmemoRefund($creditMemo);

        $storeId = $creditMemo->getStoreId();
        if (!$this->_helperConfig->getCommissionConfig('decrease_commission_creditmemo', $storeId)) {
            return $this;
        }

        $order = $creditMemo->getOrder();
        $cancelStatus = explode(',', $this->_helperConfig->getCommissionConfig('cancel_transaction_orderstatus', $storeId));
        $transaction = $this->_getTransactionModel()->load($order->getIncrementId(), 'order_number');

        if (in_array('closed', $cancelStatus) && !$order->canCreditmemo()) {
            $transaction->reduce($creditMemo);
            return $this;
        }

        if ($transaction->getId()) {
            $transaction->reduce($creditMemo);
        }
    }

    /**
     * Get Transaction Model
     * @return mixed
     */
    protected function _getTransactionModel(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Transaction');
    }

    /**
     * Get Payment Credit Model
     * @return mixed
     */
    protected function _getPaymentCreditModel(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment\Credit');
    }

    /**
     * Get Payment Model
     * @return mixed
     */
    protected function _getPaymentModel(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment');
    }

    /**
     * Refund order when using balance as store credit
     * @param $creditMemo
     * @return $this
     */
    public function creditmemoRefund($creditMemo) {
        if (!$this->_helper->isAffiliateModuleEnabled()){
            return $this;
        }
        $order = $creditMemo->getOrder();

        $paymentMethod = $this->_getPaymentCreditModel()->load($order->getId(), 'order_id');
        if ($paymentMethod->getId() && $paymentMethod->getBasePaidAmount() - $paymentMethod->getBaseRefundAmount() > 0){
            $payment = $this->_getPaymentModel()->load($paymentMethod->getPaymentId())
                ->setData('payment', $paymentMethod);
            $account = $payment->getAffiliateplusAccount();
            if ($account && $account->getId() && $payment->getId()) {
                try {
                    $refundAmount = -$creditMemo->getBaseAffiliateCredit();
                    $account->setBalance($account->getBalance() + $refundAmount)
                        ->setTotalPaid($account->getTotalPaid() - $refundAmount)
                        ->setTotalCommissionReceived($account->getTotalCommissionReceived() - $refundAmount)
                        ->save();
                    $paymentMethod->setBaseRefundAmount($paymentMethod->getBaseRefundAmount() + $refundAmount)
                        ->setRefundAmount($paymentMethod->getRefundAmount() - $creditMemo->getAffiliateCredit())
                        ->save();
                    if (abs($paymentMethod->getBasePaidAmount() - $paymentMethod->getBaseRefundAmount()) < 0.0001) {
                        $payment->setStatus(\Magestore\Affiliateplus\Model\Payment::PAYMENT_CANCELED)->save();
                    }
                } catch (\Exception $e) {

                }
            }
        }
    }
}