<?php
namespace Ktpl\Paymentcharge\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddpaymentchargeToOrder implements ObserverInterface
{
    /**
     * Set payment fee to order
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();
        $payment_charge = $quote->getPaymentCharge();
        $basepayment_charge = $quote->getBasePaymentCharge();
        if (!$payment_charge || !$basepayment_charge) {
            return $this;
        }
        //Set fee data to order
        $order = $observer->getOrder();
        $order->setData('payment_charge', $payment_charge);
        $order->setData('base_payment_charge', $basepayment_charge);

        return $this;
    }
}
