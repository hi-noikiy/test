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
class Returns extends Request
{


    /**
     * 
     * @param mage shipment_address $shippingAddress
     * @return string
     */
    public function create($shippingAddress)
    {

        $customerCompany = $shippingAddress->getCompany();

        $customerName = $shippingAddress->getFirstname().' '.$shippingAddress->getLastname();
        if (empty($customerCompany)) {

            $customerCompany = $customerName;

        }

        $region = $this->_regionModel->unsetData()->load($shippingAddress->getRegionId());

        $customerAddress = (is_array($shippingAddress->getStreet()) ?
            implode(", ", $shippingAddress->getStreet()) :
            $shippingAddress->getStreet() );

        // TODO replace with xml doc builder
        $xmlRequest = '<?xml version="1.0" encoding="UTF-8"?>
        <authorized-return xmlns="http://www.canadapost.ca/ws/authreturn">
                <service-code>' . self::RETURN_SERVICE_CODE . '</service-code>
                <returner>
                        <name>' . $customerName . '</name>
                        <company>' . $customerCompany . '</company>
                        <domestic-address>
                                <address-line-1>' . $customerAddress . '</address-line-1>
                                <city>' . $shippingAddress->getCity() . '</city>
                                <province>' . $region->getCode() . '</province>
                                <postal-code>' . $this->formatPostalCode($shippingAddress->getPostcode()) . '</postal-code>
                        </domestic-address>
                </returner>
                <receiver>
                        <name>' . $this->getStoreCompany() . '</name>
                        <company>' . $this->getStoreCompany() . '</company>
                        <domestic-address>
                                <address-line-1>' . $this->getStoreStreetOne() . '</address-line-1>
                                <city>' . $this->getStoreCity() . '</city>
                                <province>' . $this->getStoreRegionCode() . '</province>
                                <postal-code>' . $this->getStorePostcode() . '</postal-code>
                        </domestic-address>
                </receiver>
                <parcel-characteristics>
                        <weight>15</weight>
                </parcel-characteristics>
                <print-preferences>
                        <encoding>PDF</encoding>
                </print-preferences>
                <settlement-info>
                        <contract-id>' . $this->getContractId() . '</contract-id>
                </settlement-info>
        </authorized-return>';

        $url = sprintf(self::API_PATH_RETURNS, $this->getBaseUrl(), $this->getBehalfAccount(), $this->getApiCustomerNumber());


        return $this->send($url, $xmlRequest, false, $this->_header_return);

    }

    /**
     * 
     * @param string $url
     * @return string
     */
    public function getLabel($url)
    {
        return $this->send($url, '', 1, $this->_header_pdf);
    }

}