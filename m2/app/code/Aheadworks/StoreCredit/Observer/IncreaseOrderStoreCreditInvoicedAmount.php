<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class Aheadworks\StoreCredit\Observer\IncreaseOrderStoreCreditInvoicedAmount
 */
class IncreaseOrderStoreCreditInvoicedAmount implements ObserverInterface
{
    /**
     * Increase order aw_store_credit_invoiced attribute based on created invoice
     * used for event: sales_order_invoice_register
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        if ($invoice->getBaseAwStoreCreditAmount()) {
            $order->setBaseAwStoreCreditInvoiced(
                $order->getBaseAwStoreCreditInvoiced() + $invoice->getBaseAwStoreCreditAmount()
            );
            $order->setAwStoreCreditInvoiced(
                $order->getAwStoreCreditInvoiced() + $invoice->getAwStoreCreditAmount()
            );
        }
        return $this;
    }
}
