<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Paysafe\Paysafe\Model\Order\Email\Sender;

class OrderSender extends \Magento\Sales\Model\Order\Email\Sender\OrderSender
{
    /**
     * Sends order email to the customer.
     *
     * @param Order $order
     * @param bool $forceSyncMode
     * @return bool
     */
    public function send(\Magento\Sales\Model\Order $order, $forceSyncMode = false)
    {
        $paymentMethod = $order->getPayment()->getMethod();
        if(strpos($paymentMethod, 'paysafe') !== false && $order->getStatus() == 'pending_payment'){
            return false;
        }

        return parent::send($order, $forceSyncMode);
    }
}