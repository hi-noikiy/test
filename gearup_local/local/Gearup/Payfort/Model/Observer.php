<?php

class Gearup_Payfort_Model_Observer {

    var $_code = 'payfortinstallments';

    public function filterpaymentmethod(Varien_Event_Observer $observer) {
        /* call get payment method */
        $method = $observer->getEvent()->getMethodInstance();
        $result = $observer->getEvent()->getResult();
        /*   get  Quote  */
        $quote = $observer->getEvent()->getQuote();
        /*  check quote exit or not */
        if ($quote):
            $Shipping = $observer->getEvent()->getQuote()->getShippingAddress();

            $store = Mage::app()->getStore()->getStoreId();
            /* Disable Your payment method by city */
            $specificCountry = explode(',', Mage::getStoreConfig('payment/' . $this->_code . '/specificcountry', $store));


            if ($method->getCode() == $this->_code) {              
                if(in_array($Shipping->getCountryId(), $specificCountry))
                    $result->isAvailable = true;
                else
                    $result->isAvailable = false;                    
            }

        endif;
    }

}
