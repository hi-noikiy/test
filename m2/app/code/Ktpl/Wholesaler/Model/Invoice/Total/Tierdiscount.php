<?php

namespace Ktpl\Wholesaler\Model\Invoice\Total;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class Tierdiscount extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $invoice->setTierDiscount(0);
        $invoice->setBaseTierDiscount(0);

        $amount = $invoice->getOrder()->getTierDiscount();
        $invoice->setTierDiscount($amount);
        $amount = $invoice->getOrder()->getBaseTierDiscount();
        $invoice->setBaseTierDiscount($amount);

        $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getTierDiscount());
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getTierDiscount());

        return $this;
    }
}
