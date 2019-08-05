<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\CanadaPost\Helper\Rest;

/**
 * Measure Unit helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Transmit extends Request
{
    
    // TODO Identify the datatype of $manifest
    /**
     * 
     * @param type $manifest
     * @return string
     */
    public function transmit($manifest)
    {

        $originPostCode = $this->getStorePostcode();

        $xmlRequest = '<?xml version="1.0" encoding="UTF-8"?>
                        <transmit-set xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.canadapost.ca/ws/manifest" >
                          <group-ids>
                            <group-id>' . $manifest->getGroupId() . '</group-id>
                          </group-ids>
                          <requested-shipping-point>' . $originPostCode . '</requested-shipping-point>
                          <detailed-manifests>true</detailed-manifests>
                          <method-of-payment>' . $this->getApiPaymentMethod() . '</method-of-payment>
                          <manifest-address>
                            <manifest-company>' . $this->getStoreCompany() . '</manifest-company>
                            <phone-number></phone-number>
                            <address-details>
                              <address-line-1>' . $this->getStoreStreetOne() . '</address-line-1>
                              <city>' . $this->getStoreCity() . '</city>
                              <prov-state>' . $this->getStoreRegionCode() . '</prov-state>
                              <postal-zip-code>' . $originPostCode . '</postal-zip-code>
                            </address-details>
                          </manifest-address>
                        </transmit-set>';


        $url = sprintf(self::API_PATH_TRANSMIT, $this->getBaseUrl(), $this->getBehalfAccount(), $this->getApiCustomerNumber());

        return $this->send($url, $xmlRequest, false, $this->_header_transmit);

    }


}