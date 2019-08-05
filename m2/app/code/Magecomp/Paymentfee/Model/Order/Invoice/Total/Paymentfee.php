<?php
namespace Magecomp\Paymentfee\Model\Order\Invoice\Total;

use Magecomp\Paymentfee\Helper\Data as HelperData;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class Paymentfee extends AbstractTotal
{
    /**
     * @var HelperData
     */
    protected $_helperData;
	const MARGIN_OF_ERROR = 0.0005;
	
    public function __construct(HelperData $helperData)
    {
        $this->_helperData = $helperData;
    }

    public function collect (Invoice $invoice)
    {

        $order = $invoice->getOrder();
        $paymentfeeHelper = $this->_helperData;

        //work out amounts for current invoice
        $basePaymentfeeAmount = $order->getBaseMcPaymentfeeAmount() - $order->getBaseMcPaymentfeeAmountInvoiced();
        $paymentfeeAmount = $order->getMcPaymentfeeAmount() - $order->getMcPaymentfeeAmountInvoiced();
        $basePaymentfeeTaxAmount = $order->getBaseMcPaymentfeeTaxAmount() - $order->getBaseMcPaymentfeeTaxAmountInvoiced();
        $paymentfeeTaxAmount = $order->getMcPaymentfeeTaxAmount() - $order->getMcPaymentfeeTaxAmountInvoiced();

        //check if Paymentfee has already been added
        if (!$invoice->getMcPaymentfeeAmount() > 0) {
            $taxOnItemsShouldBe = $this->_addTaxItems($invoice);
            //depending on if this an updateQty call or first calculation different totals need adjustment
            if(abs($invoice->getSubtotalInclTax()-$invoice->getSubtotal()-$taxOnItemsShouldBe) > self::MARGIN_OF_ERROR) {
                $invoice->setGrandTotal($invoice->getGrandTotal() + $paymentfeeAmount);
                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $basePaymentfeeAmount);

                $invoice->setSubtotalInclTax($invoice->getSubtotalInclTax() - $paymentfeeTaxAmount);
                $invoice->setBaseSubtotalInclTax($invoice->getBaseSubtotalInclTax() - $basePaymentfeeTaxAmount);

            }else {
                $invoice->setTaxAmount($invoice->getTaxAmount() + $paymentfeeTaxAmount);
                $invoice->setBaseTaxAmount($invoice->getBaseTaxAmount() + $basePaymentfeeTaxAmount);
                $invoice->setGrandTotal($invoice->getGrandTotal() + $paymentfeeAmount + $paymentfeeTaxAmount);
                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $basePaymentfeeAmount + $basePaymentfeeTaxAmount);
            }

            //set Invoice Amounts
            $invoice->setBaseMcPaymentfeeAmount($basePaymentfeeAmount);
            $invoice->setMcPaymentfeeAmount($paymentfeeAmount);
            $invoice->setBaseMcPaymentfeeTaxAmount($basePaymentfeeTaxAmount);
            $invoice->setMcPaymentfeeTaxAmount($paymentfeeTaxAmount);

            //set Order Amounts
            $order->setBaseMcPaymentfeeAmountInvoiced($order->getBaseMcPaymentfeeAmountInvoiced() + $basePaymentfeeAmount);
            $order->setMcPaymentfeeAmountInvoiced($order->getMcPaymentfeeAmountInvoiced() + $paymentfeeAmount);
            $order->setBaseMcPaymentfeeTaxAmountInvoiced($order->getBaseMcPaymentfeeTaxAmountInvoiced() + $basePaymentfeeTaxAmount);
            $order->setMcPaymentfeeTaxAmountInvoiced($order->getMcPaymentfeeTaxAmountInvoiced() + $paymentfeeTaxAmount);

        }

        return $this;
    }

    protected function _addTaxItems($invoice) {
        $taxTotal =  0;
        foreach ($invoice->getAllItems() as $item) {
            $taxTotal += $item->getTaxAmount();
        }
        return $taxTotal;
    }

}
