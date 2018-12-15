<?php
class Ktpl_Guestshipping_Model_Observer {
    public function setShipping($evt) {
        if(!Mage::getSingleton('customer/session')->isLoggedIn()) {
            $quote = Mage::getSingleton('checkout/type_onepage')->getQuote();
            $shipping = $quote->getShippingAddress();
            $shipping->setCountryId('MU')
                     ->setShippingMethod('tablerate_bestway')
                     ->setCollectShippingRates(true);
            $shipping->save();
            $quote->save();
        }
    }
}
?>