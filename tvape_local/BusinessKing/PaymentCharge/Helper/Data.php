<?php

class BusinessKing_PaymentCharge_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * Get payment charge
     * @param string $code
     * @param Mage_Sales_Model_Quote $quote
     * @return float
     */
    public function getPaymentCharge($code, $quote = null, $customergroup = null, $store = null) {
        if (is_null($quote)) {
            $quote = Mage::getSingleton('checkout/session')->getQuote();
        }
        $amount = 0;
        if (in_array($customergroup, $this->enablecustomergroup($code,$store))) {
            $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();

            if (preg_match("/paypal/i", strval($code))) {
                $chargeType = Mage::getStoreConfig('paypal/account/charge_type');
                $chargeValue = Mage::getStoreConfig('paypal/account/charge_value');
            } else {
                $chargeType = Mage::getStoreConfig('payment/' . strval($code) . '/charge_type');
                $chargeValue = Mage::getStoreConfig('payment/' . strval($code) . '/charge_value');
            }

            if ($chargeValue) {
                if ($chargeType == "percentage") {
                    $subTotal = $address->getSubtotal();
                    $subTotal += $address->getTierDiscount();
                    $amount = $subTotal * floatval($chargeValue) / 100;
                } else {
                    $amount = floatval($chargeValue);
                }
            }
        }
        //return Mage::helper('core')->formatPrice($amount);
        return $amount;
    }
    
    public function enablecustomergroup($code,$storeid = null){
        if (preg_match("/paypal/i", strval($code))) {
            $cg = Mage::getStoreConfig('paypal/account/customergroup');
        } else {
            $cg = Mage::getStoreConfig('payment/' . strval($code) . '/customergroup',$storeid);
        }    
        $customergroup = explode(',', $cg);
        return $customergroup;
    }

}
