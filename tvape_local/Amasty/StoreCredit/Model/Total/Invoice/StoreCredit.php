<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Model_Total_Invoice_StoreCredit extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{

    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {

        $order = $invoice->getOrder();
        if ($order->getBaseAmstcredAmount() && $order->getBaseAmstcredAmountInvoiced() != $order->getBaseAmstcredAmount()) {
            $gcaLeft = $order->getBaseAmstcredAmount() - $order->getBaseAmstcredAmountInvoiced();
            if ($gcaLeft >= $invoice->getBaseGrandTotal()) {
                $baseUsed = $invoice->getBaseGrandTotal();
                $used = $invoice->getGrandTotal();

                $invoice->setBaseGrandTotal(0);
                $invoice->setGrandTotal(0);
            } else {
                $baseUsed = $order->getBaseAmstcredAmount() - $order->getBaseAmstcredAmountInvoiced();
                $used = $order->getAmstcredAmount() - $order->getAmstcredAmountInvoiced();

                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseUsed);
                $invoice->setGrandTotal($invoice->getGrandTotal() - $used);
            }

            $invoice->setBaseAmstcredAmount($baseUsed);
            $invoice->setAmstcredAmount($used);
        }

        return $this;

    }
}
