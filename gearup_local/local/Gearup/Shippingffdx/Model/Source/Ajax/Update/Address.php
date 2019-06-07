<?php

class Gearup_Shippingffdx_Model_Source_Ajax_Update_Address extends MindMagnet_PrimeCheckout_Model_Source_Ajax_Update_Address {
    
     public function toOptionArray(){
    	$optionArray = array(
            array('value' => 'region_id', 'label' => 'State/Region'),
            array('value' => 'city', 'label' => 'City'),
            array('value' => 'country_id', 'label' => 'Country'),
            array('value' => 'postcode', 'label' => 'Postcode'),
            array('value' => 'telephone', 'label' => 'Mobile'),            
	);		
	return $optionArray;
    }
}
