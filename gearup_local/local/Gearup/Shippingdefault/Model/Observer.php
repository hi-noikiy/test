<?php

class Gearup_Shippingdefault_Model_Observer {

    public function setShipping() {

        $quote = Mage::getSingleton('checkout/cart')->getQuote();


        if($getSession = Mage::getSingleton('core/session')->getInitialShipment()){
			Mage::getSingleton('core/session')->setInitialShipment(1);
			$this->setShippingSwitcher();			
    	}

        $maximumTotal = (int)Mage::getStoreConfig('payment/cashondelivery/max_price_total');
        if($quote->getGrandTotal() > $maximumTotal) {
            $method = ['method' => 'checkoutapijs'];
            Mage::getSingleton('checkout/type_onepage')
                ->savePayment($method);
        }
    }

    public function setShippingSwitcher() {    
    	Mage::getSingleton('core/session')->unsInitialShipment();
        $quote = Mage::getSingleton('checkout/cart')->getQuote();        
        $countryCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        if($countryCode =='USD' || $countryCode == 'PLN'){
            $countryCode = 'AED';
        }
		$shippingAddress = $quote->getShippingAddress()->setCountryId(substr($countryCode, 0, 2))
		 	->setCity('')
            ->setPostcode(1)
            ->setRegionId('')
            ->setRegion('')
			->setCollectShippingRates(true);            
	    $shippingAddress->save();

	    //$quote;
	  	Mage::getSingleton('checkout/cart')->getQuote()->save();        
	    Mage::getSingleton('checkout/session')->setEstimatedShippingAddressData(array(
            'country_id' => substr($countryCode, 0, 2),	  	
			'postcode'   => 1,
            'city'       => '',
            'region_id'  => '',
            'region'     => ''
             ));    
    }
}
