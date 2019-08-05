<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Paysafe\Paysafe\Observer;

use Magento\Framework\Event\ObserverInterface;

class InvoiceRegisterObserver implements ObserverInterface
{
    /**
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $paymentMethod = $order->getPayment()->getMethod();
        $orderStatus = \Paysafe\Paysafe\Model\Method\AbstractMethod::ACCEPT_STATUS;

        if (strpos($paymentMethod, 'paysafe') !== false) {
            $order->setStatus($orderStatus);
            $order->addStatusToHistory($orderStatus, '', true)->save();
        }
    }
}
