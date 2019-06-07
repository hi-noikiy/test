<?php

class Mish_Rounding_Model_Config extends Mage_Tax_Model_Config {

    /**
     * Get product price display type
     *  1 - Excluding tax
     *  2 - Including tax
     *  3 - Both
     *
     * @param   mixed $store
     * @return  int
     */
    var $countryCode = null;
    var $mishTaxRate = 0;
    
    public function getPriceDisplayType($store = null) {
        
        if ($this->customRateRequest() > 0) {
            return (int) $this->_getStoreConfig(self::CONFIG_XML_PATH_PRICE_DISPLAY_TYPE, $store);
        }

        return 1;
    }

    /**
     * @param Mage_Sales_Model_Quote_Address|null $shippingAddress
     * @return float|int
     */
    function customRateRequest($shippingAddress = null) {
        $countryCode = ($shippingAddress) ? $shippingAddress->getCountryId() 
            : Mage::app()->getStore()->getCurrentCurrencyCode();
        $country_id = substr($countryCode, 0, 2);
                
        if ($this->countryCode != $country_id) {
            $calculation = new Mage_Tax_Model_Calculation();
            $request = new Varien_Object();
            $request->setCountryId($country_id);
            $request->setProductClassId(2);
            $request->setCustomerClassId(3);
            $this->countryCode = $country_id;
            return $this->mishTaxRate = $calculation->getRate($request);
        }
        return $this->mishTaxRate;
    }

}
