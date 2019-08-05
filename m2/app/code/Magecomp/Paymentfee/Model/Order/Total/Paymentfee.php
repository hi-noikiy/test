<?php
namespace Magecomp\Paymentfee\Model\Order\Invoice\Total;

use Magento\Sales\Model\Order\Order;
use Magento\Sales\Model\Order\Total\AbstractTotal;

class Paymentfee extends AbstractTotal {

    public function collect(Order $order) {

        $basePaymentfeeAmount = $order->getOrder()->getBaseMcPaymentfeeAmount();
        $paymentfeeAmount = $order->getOrder()->getMcPaymentfeeAmount();

        $basePaymentfeeTaxAmount = $order->getOrder()->getBaseMcPaymentfeeTaxAmount();
        $paymentfeeTaxAmount = $order->getOrder()->getMcPaymentfeeTaxAmount();

        $order->setBaseMcPaymentfeeAmount($basePaymentfeeAmount);
        $order->setMcPaymentfeeAmount($paymentfeeAmount);

        $order->setGrandTotal($order->getGrandTotal() + $paymentfeeAmount);
        $order->setBaseGrandTotal($order->getBaseGrandTotal() + $basePaymentfeeAmount);

        return $this;
    }
}