<?php
namespace Magecomp\Paymentfee\Model\Order\Creditmemo\Total;

use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class Paymentfee extends AbstractTotal {

    public function collect(Creditmemo $creditmemo) {

        $order = $creditmemo->getOrder();
        $invoice = $creditmemo->getInvoice();

        //work out amounts for current creditmemo
        if ($invoice) {
            $basePaymentfeeAmount = $invoice->getBaseMcPaymentfeeAmount();
            $paymentfeeAmount = $invoice->getMcPaymentfeeAmount();
            $basePaymentfeeTaxAmount = $invoice->getBaseMcPaymentfeeTaxAmount();
            $paymentfeeTaxAmount = $invoice->getMcPaymentfeeTaxAmount();
        } else {
            $basePaymentfeeAmount = $order->getBaseMcPaymentfeeAmount() - $order->getBaseMcPaymentfeeAmountRefunded();
            $paymentfeeAmount = $order->getMcPaymentfeeAmount() - $order->getMcPaymentfeeAmountRefunded();
            $basePaymentfeeTaxAmount = $order->getBaseMcPaymentfeeTaxAmount() - $order->getBaseMcPaymentfeeTaxAmountRefunded();
            $paymentfeeTaxAmount = $order->getMcPaymentfeeTaxAmount() - $order->getMcPaymentfeeTaxAmountRefunded();
        }

        //set Creditmemo Amounts
        $creditmemo->setBaseMcPaymentfeeAmount($basePaymentfeeAmount);
        $creditmemo->setMcPaymentfeeAmount($paymentfeeAmount);
        $creditmemo->setBaseMcPaymentfeeTaxAmount($basePaymentfeeTaxAmount);
        $creditmemo->setMcPaymentfeeTaxAmount($paymentfeeTaxAmount);
        $creditmemo->setTaxAmount($creditmemo->getTaxAmount() + $paymentfeeTaxAmount);
        $creditmemo->setBaseTaxAmount($creditmemo->getBaseTaxAmount() + $basePaymentfeeTaxAmount);
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $paymentfeeAmount + $paymentfeeTaxAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $basePaymentfeeAmount + $basePaymentfeeTaxAmount);

        //set Order Amounts
        $order->setBaseMcPaymentfeeAmountRefunded($order->getBaseMcPaymentfeeAmountRefunded()+$basePaymentfeeAmount);
        $order->setMcPaymentfeeAmountRefunded($order->getMcPaymentfeeAmountRefunded()+$paymentfeeAmount);
        $order->setBaseMcPaymentfeeTaxAmountRefunded($order->getBaseMcPaymentfeeTaxAmountRefunded()+$basePaymentfeeTaxAmount);
        $order->setMcPaymentfeeTaxAmountRefunded($order->getMcPaymentfeeTaxAmountRefunded()+$paymentfeeTaxAmount);

        return $this;
    }
}