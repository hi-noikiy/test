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
class Shipment extends Request
{


    /**
     * @param mage shipment_address $shippingAddress
     * @param Magento\Quote\Model\Quote $quote
     * @param int $group_id
     * @param array $params
     * @return string
     */
    public function _createCustomsNode($shippingAddress, $quote, $group_id, $params)
    {

        $xml = '';

        if($shippingAddress->getCountryId() != self::COUNTRY_CANADA) {

            $item_xml = '';

            foreach ($quote->getAllItems() as $item) {

                $product = $this->getProduct($item);


                // TODO would we want to touch anything else?
                if ($product->getTypeId() != 'simple') {
                  continue;
                }

                $item_xml .= '
                <item>
                <customs-number-of-units>' . (int)$item->getQty() . '</customs-number-of-units>
                <customs-description><![CDATA[' . substr($item->    getName(), 0, self::MAXIMUM_SKU_LENGTH) . ']]></customs-description>
                <sku>' . substr($item->getSku(), 0, self::MAXIMUM_SKU_LENGTH) . '</sku>
                ';

                $hsTariffCode = $product->getHsTariffCode();

                if (!empty($hsTariffCode)) {

                    $item_xml .= '<hs-tariff-code>' . $hsTariffCode . '</hs-tariff-code>';

                }

                $customsWeight = $item->getWeight();

                $catalogMeasureUnit = $this->getConfigValue(\CollinsHarper\Core\Helper\Measure::XML_PATH_DEFAULT_MEASURE_UNIT);
                if($catalogMeasureUnit != \Zend_Measure_Weight::KILOGRAM) {
                    $customsWeight =  $this->_carrierHelper->convertMeasureWeight(
                        $customsWeight,
                        $catalogMeasureUnit,
                        \Zend_Measure_Weight::KILOGRAM
                    );
                }

                $origin_country = $product->getData('country_of_manufacture');

                $origin_province = $product->getAttributeText('origin_province');

                $item_xml .= '
                <unit-weight>' . $customsWeight . '</unit-weight>';

                $item_xml .= '
                <customs-value-per-unit>' . $item->getPrice() . '</customs-value-per-unit>';

                if (!empty($origin_country)) {
                    if ($origin_country != self::COUNTRY_CANADA) {
                        $item_xml .= '<country-of-origin>' . $origin_country . '</country-of-origin>';
                    } else if ($origin_country == self::COUNTRY_CANADA && !empty($origin_province)) {
                        $item_xml .= '<country-of-origin>' . $origin_country . '</country-of-origin>';

                        $province_code = $this->_regionModel->unsetData()
                            ->getCollection()
                            ->addFieldToFilter('default_name', $origin_province)
                            ->getFirstItem()
                            ->getCode();

                        $item_xml .= '<province-of-origin>'.$province_code.'</province-of-origin>';
                    }
                }

                $item_xml .= '</item>
                ';

            }

            $item_xml = '
            <sku-list>
            ' . $item_xml . '
            </sku-list>
            ';



            $currencyConversion = 1;

            // TODO we do not convert currently
            $destination_currency = $this->_getDefaultCurrencyFromCountry($shippingAddress->getCountryId());

            try {
          //      $currencyConversion = $helper->_getConversionRate($base_currency, $destination_currency);

            } catch (exception $e) {
                // we dont care..
            }

          //  $converted_value = number_format($currencyConversion*$customs_value,2);
            //$cost = (double)$cost * $this->_getBaseCurrencyRate($responseCurrencyCode);


            $reason_for_export = $this->getExportReason();

            $xml .= '
            <customs>
                <currency>' . $destination_currency . '</currency>
                <conversion-from-cad>' . $currencyConversion . '</conversion-from-cad>
                <reason-for-export>' . $reason_for_export . '</reason-for-export>';
            if ($reason_for_export == self::REASON_FOR_EXPORT_OTHER) {
                $xml .= '<other-reason>' . $this->getExportReasonOther() . '</other-reason>';
            }
            $xml .=
                $item_xml.'
            </customs>';

        }

        return $xml;

    }


    /**
     *
     * @param mage shipment_address $shippingAddress
     * @param object mage quote $quote
     * @param int $group_id
     * @param array $params
     * @return type 
     */
    public function create($shippingAddress, $quote, $group_id = 1, $params = array())
    {

        $xmlRequest = $this->composeXml($shippingAddress, $quote, $group_id, $params);

        if ($this->isContract()) {

            $url = sprintf(self::API_PATH_CONTRACT_SHIPMENT, $this->getBaseUrl(), $this->getBehalfAccount(), $this->getApiCustomerNumber());

            $headers = $this->_header_shipment;

        } else {

            $url = sprintf(self::API_PATH_NONCONTRACT_SHIPMENT, $this->getBaseUrl(), $this->getBehalfAccount());

            $headers = $this->_header_non_shipment;
        }

        return $this->send($url, $xmlRequest, false, $headers);

    }


    /**
     *
     * @param string $cpShipmentId
     */
    public function void($cpShipmentId)
    {
        $shipmentLink = $this->objectFactory->setClass('CollinsHarper\CanadaPost\Model\Link')->create()->getCollection()
            ->addFieldToFilter('cp_shipment_id', $cpShipmentId)
            ->addFieldToFilter('rel', 'self')
            ->getFirstItem();
        if (!$shipmentLink->getId()) {
            // TODO potentially there is  no link as it was a  bad shipment?
            //throw new \Exception("Shipment has already been deleted.");
            return true;
        }

        $url = $shipmentLink->getUrl();

        $headers = array(
            'Accept:' . $shipmentLink->getMediaType()
        );

        return $this->send($url, null, false, $headers, self::API_METHOD_DELETE);

    }


    /**
     *
     * @param mage shipment_address  $shippingAddress
     * @param Magento\Quote\Model\Quote $quote
     * @param int $group_id
     * @param array $params
     * @return string
     */
    private function composeXml($shippingAddress, $quote, $group_id, $params)
    {

        $customs_node = $this->_createCustomsNode($shippingAddress, $quote, $group_id, $params);

        $customerEmail = $shippingAddress->getEmail();

        if (empty($customerEmail)) {

            $customerEmail = $quote->getCustomerEmail();

        }

        if (!empty($params['cp_office_id'])) {

            $this->_chLogged->info(__METHOD__ .    'office');
            $office = $this->objectFactory->setClass('CollinsHarper\CanadaPost\Model\Office')->create()->load($params['cp_office_id']);

            $address = array(
                'city' => $office->getCity(),
                'province' => $office->getProvince(),
                'address' => $office->getAddress(),
                'country' => self::COUNTRY_CANADA,
                'postal_code' => $this->formatPostalCode($office->getPostalCode()),
            );

            $destination_name = $office->getCpOfficeName();

        } else {

            $street = $shippingAddress->getStreet();

            $address = array(
                'city' => $shippingAddress->getCity(),
                'province' => $this->_regionFactory->create()->load($shippingAddress->getRegionId())->getCode(),
                'address' => (is_array($street)) ? implode(', ', $street) : $street,
                'country' => $shippingAddress->getCountryId(),
                'postal_code' => $this->formatPostalCode($shippingAddress->getPostcode()),
            );

            $destination_name = $shippingAddress->getCompany();

        }

        $xmlRequest = '<?xml version="1.0" encoding="UTF-8"?>';

        if ($this->isContract()) {

            // TODO magento date please
            $xmlRequest .= '<shipment xmlns="http://www.canadapost.ca/ws/shipment">
                                <group-id>' . $group_id . '</group-id>
                                <requested-shipping-point>' . $this->getStorePostcode() . '</requested-shipping-point>
                                <expected-mailing-date>' . date('Y-m-d') . '</expected-mailing-date>';

        } else {

            $xmlRequest .= '<non-contract-shipment xmlns="http://www.canadapost.ca/ws/ncshipment">';


        }

        $xmlRequest .= '
                <delivery-spec>
                        <service-code>' . (!empty($params['service_code']) ? $params['service_code'] : self::DEFAULT_SERVICE_CODE) . '</service-code>
                        <sender>
                                <name><![CDATA[' .  $this->getStoreCompany() . ']]></name>
                                <company><![CDATA[' .  $this->getStoreCompany() . ']]></company>
                                <contact-phone>' . $this->getStorePhone() . '</contact-phone>
                                <address-details>
                                        <address-line-1>' . $this->getStoreStreetOne() . '</address-line-1>
                                        <city>' . $this->getStoreCity() . '</city>
                                        <prov-state>' . $this->getStoreRegionCode() . '</prov-state>';

        if ($this->isContract()) {

            $xmlRequest .= '<country-code>' . $this->getStoreCountry() . '</country-code>';

        }

        $xmlRequest .= '
                                        <postal-zip-code>' . $this->getStorePostcode() . '</postal-zip-code>
                                </address-details>
                        </sender>
                        <destination>
                                <name><![CDATA[' . $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname() . ']]></name>';

        if (!empty($destination_name)) {

            $xmlRequest .=     '<company><![CDATA[' . $destination_name . ']]></company>';

        }

        $options = '';

        if(!empty($params['options'])) {
            $options = '<options>' . $params['options'] . '</options>';
        }

        $xmlRequest .= '
                                <address-details>
                                        <address-line-1>' . $address['address'] . '</address-line-1>
                                        <city>' . $address['city'] . '</city>
                                        <prov-state>' . (!empty($address['province']) ? $address['province'] : ' . ') . '</prov-state>
                                        <country-code>' . $address['country'] . '</country-code>
                                        <postal-zip-code>' . $this->formatPostalCode($address['postal_code']) . '</postal-zip-code>
                                </address-details>
                                <client-voice-number>'.$shippingAddress->getTelephone() . '</client-voice-number>
                        </destination>
                        ' . $options . '
                        <parcel-characteristics>
                                <weight>' . (!empty($params['weight']) ? $params['weight'] : '' ) . '</weight>';

        if (!empty($params['box'])) {

            $xmlRequest .= '
                                                <dimensions>
                                                        <length>' . $params['box']['l'] . '</length>
                                                        <width>' . $params['box']['w'] . '</width>
                                                        <height>' . $params['box']['h'] . '</height>
                                                </dimensions>';

        } else {

            $xmlRequest .= '
                                                <dimensions>
                                                        <length>' . $this->getDefaultLength() . '</length>
                                                        <width>' . $this->getDefaultWidth() . '</width>
                                                        <height>' . $this->getDefaultHeight() . '</height>
                                                </dimensions>';

        }


        $notifyOnShipment = $this->getConfigValueForXml(self::XML_PATH_NOTIFY_ON_SHIPMENT);
        $notifyOnException = $this->getConfigValueForXml(self::XML_PATH_NOTIFY_ON_EXCEPTION);
        $notifyOnDelivery = $this->getConfigValueForXml(self::XML_PATH_NOTIFY_ON_DELIVERY);

        $this->_chLogged->info(__METHOD__ .    'params');

        if (isset($params['options']) && strpos($params['options'], \CollinsHarper\CanadaPost\Helper\Option::OPTIONS_D2PO) !== false) {
            $notifyOnShipment = 'true';
            $notifyOnException = 'true';
            $notifyOnDelivery = 'true';
        }

        $xmlRequest .= '
                                <unpackaged>false</unpackaged>
                                <mailing-tube>false</mailing-tube>
                        </parcel-characteristics>
                        <notification>
                                <email>' . $customerEmail . '</email>
                                <on-shipment>'  . $notifyOnShipment  . '</on-shipment>
                                <on-exception>' . $notifyOnException . '</on-exception>
                                <on-delivery>'  . $notifyOnDelivery  . '</on-delivery>
                        </notification>
                        <references>
                                <customer-ref-1>' . $params['_order']->getIncrementId() . '</customer-ref-1>
                        </references>
                        '.$customs_node;

        if ($this->isContract()) {

            $xmlRequest .= '
                        <print-preferences>
                                <output-format>' . $this->getLabelOutput() . '</output-format>
                        </print-preferences>';
        }

        $xmlRequest .= '
                        <preferences>
                                <show-packing-instructions>true</show-packing-instructions>
                                <show-postage-rate>false</show-postage-rate>
                                <show-insured-value>true</show-insured-value>
                        </preferences>';

        if ($this->isContract()) {

            $xmlRequest .= '<settlement-info>
                                <contract-id>' . $this->getContractId() . '</contract-id>
                                <intended-method-of-payment>'. $this->getApiPaymentMethod() . '</intended-method-of-payment>
                            </settlement-info>';

        }

        $xmlRequest .= '</delivery-spec>';

        if ($this->isContract()) {

            $xmlRequest .= '</shipment>';

        } else {

            $xmlRequest .= '</non-contract-shipment>';

        }
        $this->_chLogged->info(__METHOD__ .  $xmlRequest);

        return $xmlRequest;

    }

}