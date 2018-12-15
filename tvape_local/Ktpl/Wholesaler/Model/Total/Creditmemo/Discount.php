<?php
class Ktpl_Wholesaler_Model_Total_Creditmemo_Discount extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
//        $order = $creditmemo->getOrder();
//        $amount = Mage::helper('wholesaler')->calculateTax($order);
//        if ($amount) {
//            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $amount);
//            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $amount);
//        }
        
        $creditmemo->setTierDiscount(0);
        $creditmemo->setBaseTierDiscount(0);
        
        $amount = $creditmemo->getOrder()->getTierDiscount();        
        $creditmemo->setTierDiscount($amount);
        
        $amount = $creditmemo->getOrder()->getBaseTierDiscount();
        $creditmemo->setBaseTierDiscount($amount);
        
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getTierDiscount());
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getBaseTierDiscount());
        return $this;
    }
}
