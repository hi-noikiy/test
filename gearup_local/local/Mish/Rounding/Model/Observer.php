<?php

/**
 * Class Mish_Rounding_Model_Observer
 */
class Mish_Rounding_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     * @throws Exception
     */
    public function roundTotal(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        $items = $quote->getAllVisibleItems();
        $amount = 0;

        $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $currencyObj = new Mage_Directory_Model_Currency;
        $currencyObj->setCurrencyCode($currentCurrencyCode);
        $roundHelper = Mage::helper('rounding');

        /** @var Mage_Sales_Model_Quote_Item $item */
        foreach ($items as $item) {
            $amount += $item->getRowTotalInclTax();
            $item->setDiscountAmount($roundHelper->process($currencyObj, $item->getDiscountAmount()))->save();
        }

        $shipAddr = $quote->getShippingAddress();
        if ($quote->getCustomer()) {
            $taxRate = Mage::getModel('tax/config')->customRateRequest($shipAddr);
        } else {
            $taxRate = Mage::getModel('tax/config')->customRateRequest();
        }
        $discountAmount = $roundHelper->process($currencyObj, $shipAddr->getDiscountAmount());
        $shipAddr->setDiscountAmount($discountAmount);

        $shipCost = (float)Mage::helper('rounding')
            ->getRoundedTaxShipment($shipAddr->getBaseShippingInclTax(), $shipAddr, false);

        $codFee = $shipAddr->getCodFee();

        $amount += $shipCost;
        $amount = $roundHelper->process($currencyObj, $amount + $discountAmount + $codFee);

        $tax = round($amount / (100 + $taxRate) * $taxRate, 2);

        $quote
            ->setGrandTotal($amount)
            ->setSubtotalWithDiscount($quote->getSubtotal() + $discountAmount)
            ->save();

        $shipAddr
            ->setShippingAmount($shipCost)
            ->setGrandTotal($amount)
            ->setShippingInclTax($shipCost)
            ->setTaxAmount($tax)
            ->save();

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     * @throws Exception
     */
    public function correctInvoiceTotals(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Invoice $invoice */
        $invoice = $observer->getEvent()->getInvoice();
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();

        $invoice
            ->setSubtotal($order->getSubtotalInclTax())
            ->setGrandTotal($order->getGrandTotal())
            ->setShippingAmount($order->getShippingInclTax())
            ->setShippingTaxAmount($order->getShippingTaxAmount())
            ->setTaxAmount($order->getTaxAmount())
            ->save();


        return $this;
    }
}