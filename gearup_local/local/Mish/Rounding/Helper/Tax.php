<?php

class Mish_Rounding_Helper_Tax extends Mage_Tax_Helper_Data
{
    public function getPrice($product, $price, $includingTax = null, $shippingAddress = null, 
                             $billingAddress = null, $ctc = null, $store = null, 
                             $priceIncludesTax = null, $roundPrice = true)
    {
        $price = parent::getPrice($product, $price, $includingTax, $shippingAddress, $billingAddress, 
            $ctc, $store, $priceIncludesTax, $roundPrice);
        if ($roundPrice) {
            $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
            $currencyObj = new Mage_Directory_Model_Currency;
            $currencyObj->setCurrencyCode($currentCurrencyCode);

            $price = Mage::helper('rounding')->process($currencyObj, $price);
        }
        return $price;
    }
}