<?php
class Ktpl_Wholesaler_Model_Total_Quote_Discount extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
    public function __construct()
    {
        $this->setCode('wholesaler_discount');
    }

    /**
     * Get label
     *
     * @return string 
     */
    public function getLabel()
    {
        return Mage::helper('wholesaler')->getlabel();
    }

    /**
     * Collect totals information about insurance
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);
        if (($address->getAddressType() == 'billing')) {
            return $this;
        }
        
        $grandTotal = $address->getGrandTotal();
        $baseGrandTotal = $address->getBaseGrandTotal();
        $baseTotal = $address->getBaseSubtotal();
        $customergroup = 0;
        if(Mage::getSingleton('customer/session')->isLoggedIn()) {
            $customerData = Mage::getSingleton('customer/session')->getCustomer();
            $customergroup = $customerData->getGroupId();
        }
        $discount = Mage::helper('wholesaler')->calculateCustomDiscount($baseTotal,$customergroup);
        
        if ($discount) {
            
            $totals = array_sum($address->getAllTotalAmounts())-$address->getTax_amount();
            $baseTotals = array_sum($address->getAllBaseTotalAmounts())-$address->getTax_amount(); 
            
            $address->setTierDiscount(-$totals * $discount / 100);
            $address->setBaseTierDiscount(-$baseTotals * $discount / 100);

            $address->setGrandTotal($grandTotal + $address->getTierDiscount());
            $address->setBaseGrandTotal($baseGrandTotal + $address->getBaseTierDiscount());
        
        }


        return $this;
    }

    /**
     * Add giftcard totals information to address object
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     */
        
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        if (($address->getAddressType() == 'billing')) {
            return;
        }
        $discount_amount =  $address->getTierDiscount();
        
        if ($discount_amount != 0) {
            $address->addTotal(array(
                'code'  => 'wholesaler',//$this->getCode(),
                'title' => Mage::helper('wholesaler')->getlabel(),
                'value' => $discount_amount
            ));
        }

        return $this;
    }
    
}
?>