<?php
class Ktpl_Wholesaler_Model_Total_Invoice_Discount extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
//        $order = $invoice->getOrder();
//        $taxAmount = Mage::helper('wholesaler')->calculateTax($order);
//        if ($taxAmount) {
//            $invoice->setGrandTotal($invoice->getGrandTotal() + $taxAmount);
//            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $taxAmount);
//        }
        
        $invoice->setTierDiscount(0);
        $invoice->setBaseTierDiscount(0);
        
        $amount = $invoice->getOrder()->getTierDiscount();        
        $invoice->setTierDiscount($amount);
        
        $amount = $invoice->getOrder()->getBaseTierDiscount();
        $invoice->setBaseTierDiscount($amount);
        
        $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getTierDiscount());
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getBaseTierDiscount());
        return $this;
    }
}
    