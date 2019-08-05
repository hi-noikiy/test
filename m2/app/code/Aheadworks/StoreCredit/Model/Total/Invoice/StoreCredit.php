<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model\Total\Invoice;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;
use Aheadworks\StoreCredit\Model\Config;

/**
 * Class Aheadworks\StoreCredit\Model\Total\Invoice\StoreCredit
 */
class StoreCredit extends AbstractTotal
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     *  {@inheritDoc}
     */
    public function collect(Invoice $invoice)
    {
        $invoice->setAwUseStoreCredit(false);
        $invoice->setAwStoreCreditAmount(0);
        $invoice->setBaseAwStoreCreditAmount(0);

        $order = $invoice->getOrder();
        if ($order->getBaseAwStoreCreditAmount()
            && $order->getBaseAwStoreCreditInvoiced()
            != $order->getBaseAwStoreCreditAmount()
        ) {
            $awScLeft = $order->getBaseAwStoreCreditAmount() - (float)$order->getBaseAwStoreCreditInvoiced();
            $baseUsed = $invoice->getBaseGrandTotal();
            $used = $invoice->getGrandTotal();
            $baseGrandTotal = 0;
            $grandTotal = 0;

            if (!$this->config->isApplyingStoreCreditOnTax($order->getStore()->getWebsiteId())) {
                $baseUsed -= $invoice->getBaseTaxAmount();
                $used -= $invoice->getTaxAmount();
                $baseGrandTotal += $invoice->getBaseTaxAmount();
                $grandTotal += $invoice->getTaxAmount();
            }
            if (!$this->config->isApplyingStoreCreditOnShipping($order->getStore()->getWebsiteId())) {
                $baseUsed -= $invoice->getBaseShippingAmount();
                $used -= $invoice->getShippingAmount();
                $baseGrandTotal += $invoice->getBaseShippingAmount();
                $grandTotal += $invoice->getShippingAmount();
            }

            if (abs($awScLeft) >= $baseUsed) {
                $invoice->setBaseGrandTotal($baseGrandTotal);
                $invoice->setGrandTotal($grandTotal);
            } else {
                $baseUsed = abs($order->getBaseAwStoreCreditAmount()) - abs($order->getBaseAwStoreCreditInvoiced());
                $used = abs($order->getAwStoreCreditAmount()) - abs($order->getAwStoreCreditInvoiced());

                $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() - $baseUsed);
                $invoice->setGrandTotal($invoice->getGrandTotal() - $used);
            }

            if ($baseUsed > 0) {
                $invoice->setAwUseStoreCredit($order->getAwUseStoreCredit());
                $invoice->setBaseAwStoreCreditAmount(-$baseUsed);
                $invoice->setAwStoreCreditAmount(-$used);
            }
        }
        return $this;
    }
}
