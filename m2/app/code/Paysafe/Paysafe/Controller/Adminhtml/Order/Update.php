<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Paysafe\Paysafe\Controller\Adminhtml\Order;

class Update extends \Magento\Backend\App\Action
{
    protected $order;
    protected $payment;
    protected $helperCore;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Paysafe\Paysafe\Controller\Payment $payment,
        \Paysafe\Paysafe\Helper\Core $helperCore,
        \Magento\Sales\Model\Order $order
        ) {
        parent::__construct($context);
        $this->helperCore = $helperCore;
        $this->order = $order;
        $this->payment = $payment;

        $this->PSStatus = \Paysafe\Paysafe\Model\Method\AbstractMethod::PS_STATUS;
    }

    /**
     * update a payment status
     * @return void
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $this->order->load($orderId);
        $realOrderId = $this->order->getRealOrderId();

        $payment = $this->order->getPayment();
        $paymentMethod = $payment->getMethod();
        $this->payment->paymentMethod = $this->payment->createPaymentMethodObjectByPaymentMethod($paymentMethod);
        $updateStatusCredentials = $this->helperCore->getGeneralCredentials();
        $updateStatusCredentials['environment'] = $this->payment->paymentMethod->getSpecificConfiguration('environment');

        $transactionId = $this->order->getPayment()->getAdditionalInformation('TRANSACTION_ID');
        $setlementId = '';
        $refundId = '';
        if ($this->order->getPayment()->getAdditionalInformation('AUTHORIZATION_STATUS') == 'COMPLETED' &&
            $this->order->getPayment()->getAdditionalInformation('SETTLEMENT_ID') != '') {
            $setlementId = $this->order->getPayment()->getAdditionalInformation('SETTLEMENT_ID');
        }
        if ($this->order->getPayment()->getAdditionalInformation('REFUND_ID') != '') {
            $refundId = $this->order->getPayment()->getAdditionalInformation('REFUND_ID');
        }
        $statusResponse = $this->helperCore->updateStatus($transactionId, $setlementId, $refundId, $updateStatusCredentials);

        if (isset($statusResponse['status'])) {
            if ($this->order->getPayment()->getAdditionalInformation('AUTHORIZATION_STATUS') == 'COMPLETED' && $this->order->getPayment()->getAdditionalInformation('SETTLEMENT_STATUS') != 'COMPLETED') {
                if ($statusResponse['status'] != $this->order->getPayment()->getAdditionalInformation('SETTLEMENT_STATUS')) {
                    $this->order->getPayment()->setAdditionalInformation('SETTLEMENT_STATUS', $statusResponse['status']);
                    if ($statusResponse['status'] == 'COMPLETED') {
                        $orderStatus = \Paysafe\Paysafe\Model\Method\AbstractMethod::ACCEPT_STATUS;
                        $this->order->setStatus($orderStatus);
                        $this->payment->createInvoice($this->order);
                    } else {
                        $orderStatus = \Paysafe\Paysafe\Model\Method\AbstractMethod::SP_STATUS;
                        $this->order->setState('new')->setStatus($orderStatus)->save();
                        $this->order->addStatusToHistory($orderStatus, '', true)->save();
                    }
                    $successMessage = 'The payment status has been successfully updated.';  
                } else {
                    $successMessage = 'No status will be updated';
                }
            } elseif ($this->order->getPayment()->getAdditionalInformation('SETTLEMENT_STATUS') == 'COMPLETED') {
                if ($statusResponse['status'] != $this->order->getPayment()->getAdditionalInformation('REFUND_STATUS')) {
                    $this->order->getPayment()->setAdditionalInformation('REFUND_STATUS', $statusResponse['status'])->save();
                    $successMessage = 'The payment status has been successfully updated.';  
                } else {
                    $successMessage = 'No status will be updated';
                }
            } else {
                if ($statusResponse['status'] != $this->order->getPayment()->getAdditionalInformation('AUTHORIZATION_STATUS')) {
                    $availableToSettle = $statusResponse['availableToSettle'];
                    if ($availableToSettle > 0) {
                        $this->order->getPayment()->setAdditionalInformation('AUTHORIZATION_STATUS', $statusResponse['status']);
                        $this->order->setState('new')->setStatus($this->PSStatus)->save();
                        $this->order->addStatusToHistory($this->PSStatus, '', true)->save();
                    } else {
                        $this->order->getPayment()->setAdditionalInformation('AUTHORIZATION_STATUS', $statusResponse['status']);
                    }    
                } else {
                    $successMessage = 'No status will be updated';
                }
            }
            $this->payment->redirectSuccessOrderDetail($successMessage, $orderId);
        } else {
            $detailError = false;
            if (isset($statusResponse['error']['message'])) {
                $detailError = $statusResponse['error']['message'];
            }
            $this->payment->redirectErrorOrderDetail('Order status can not be updated', $orderId, $detailError);
        }
    }

}