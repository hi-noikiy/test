<?php
/**
 * Frontier Force
 */

class FFDX_Shipping_Model_Carrier extends Mage_Shipping_Model_Carrier_Tablerate
{
    /**
     * Max entry length;
     */
    const MAX_ENTRY_LENGTH = 40;

    /**
     * Gets carrier configuration
     *
     * @param $key
     * @param string $group
     * @return string
     */
    protected static function getCarrierConfig($key, $group = 'general')
    {
        return trim(Mage::getStoreConfig(sprintf('ffdxshipping/%s/%s', $group, $key)));
    }

    /**
     * @see Mage_Shipping_Model_Carrier_Interface
     * @param Mage_Shipping_Model_Rate_Request $request
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        return parent::collectRates($request);
    }

    /**
     * @see Mage_Shipping_Model_Carrier_Interface
     * @return boolean
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * @see Mage_Shipping_Model_Carrier_Interface
     * @return boolean
     */
    public function isShippingLabelsAvailable()
    {
        return true;
    }

    /**
     * @see Mage_Shipping_Model_Carrier_Interface
     * @param Varien_Object $params
     * @return array
     */
    public function getContentTypes(Varien_Object $params)
    {
        return array('normal' => 'Normal Package');
    }
    
  
    /**
     * @see Mage_Shipping_Model_Carrier_Interface
     * @param Mage_Shipping_Model_Shipment_Request $request
     * @return Varien_Object|void
     */
    public function requestToShipment(Mage_Shipping_Model_Shipment_Request $request)
    {
        $packages = $request->getPackages();

        if (!is_array($packages) || !$packages) {
            Mage::throwException(Mage::helper('ffdxshipping')->__('No packages for request'));
        }

        try {
            // We manage only first package:
            $response = $this->_doShipmentRequest($request, current($packages));
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('ffdxshipping')->__('Can not execute remote tasks. ' . $e->getMessage()));
        }

        return $response;
    }

    /**
     * Printout
     *
     * @param $url
     * @return binary
     */
    protected function retrievePrintout($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        if(curl_errno($ch))
        {
            echo 'error:' . curl_error($ch);
            die();
        }
        curl_close($ch);

        return $data;
    }

    /**
     * Connects to external API
     *
     * @param $request
     * @param $package
     * @return Varien_Object
     */
    protected function _doShipmentRequest($request, $package)
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><WSGET />');
        $order = $incrementId = $request->getOrderShipment()->getOrder();
        $incrementId = $order->getIncrementId();
        $dateTime = Mage::helper('core')->formatDate(null, 'short', true);
        $dimensions = & $package['params'];
        $recipientCountry = $request->getRecipientAddressCountryCode();
        $recipientCountryCode = $recipientCountry;

        // Country code and name:
        $countries = Mage::getResourceModel('directory/country_collection')->loadData()->toOptionArray(false);
        foreach ($countries as $country) {
            if ($country['value'] == $recipientCountry) {
                $recipientCountry = $country['label'];
                break;
            }
        }

        // Currency, rounding
        $targetCurrency = Mage::getModel('directory/currency')->load($order->getOrderCurrencyCode());

        $accessRequest = $xml->addChild('AccessRequest');
        $accessRequest->addChild('WSVersion', 'WS1.3');
        $accessRequest->addChild('FileType', '19');
        $accessRequest->addChild('Action', 'upload');
        $accessRequest->addChild('EntityID', self::getCarrierConfig('entity_id'));
        $accessRequest->addChild('EntityPIN', self::getCarrierConfig('entity_pin'));
        $accessRequest->addChild('MessageID', self::getCarrierConfig('message_id'));
        $accessRequest->addChild('AccessID', self::getCarrierConfig('access_id'));
        $accessRequest->addChild('AccessPIN', self::getCarrierConfig('access_pin'));
        $accessRequest->addChild('CreatedDateTime', $dateTime);

        $cc = $xml->addChild('CMDetail')->addChild('CC');
        $cc->addChild('CCIsValidate', self::getCarrierConfig('is_validate', 'information') ? 'Y' : 'N');
        $cc->addChild('CCLabelReq', self::getCarrierConfig('label_required', 'information') ? 'Y' : 'N');
        $cc->addChild('CCAccCardCode', self::getCarrierConfig('acc_card_code', 'information'));
        $cc->addChild('CCCustDeclaredWeight', $dimensions['weight']);

        if ($dimensions['weight_units'] == 'KILOGRAM') {
            $cc->addChild('CCWeightMeasure', 'Kgs');
        } else if ($dimensions['weight_units'] == 'POUND') {
            $cc->addChild('CCWeightMeasure', 'Lbs');
        }

        $ccSenderName = self::getCarrierConfig('company_name', 'shipper_data');
        $ccSenderAdd1 = self::getCarrierConfig('contact_name', 'shipper_data');
        $ccSenderAdd2 = self::getCarrierConfig('address1', 'shipper_data');
        $ccSenderAdd3 = self::getCarrierConfig('address2', 'shipper_data');
        $CCSenderLocCode = self::getCarrierConfig('address_country_code', 'shipper_data');
        $CCSenderLocName = self::getCarrierConfig('city', 'shipper_data');
        $CCSenderLocState = self::getCarrierConfig('address_state_or_province', 'shipper_data');
        $CCSenderLocPostcode = self::getCarrierConfig('address_postal_code', 'shipper_data');
        $CCSenderLocCtryCode = $CCSenderLocCode;
        $CCSenderContact  = self::getCarrierConfig('sender_contact_person', 'shipper_data');
        $CCSenderPhone = self::getCarrierConfig('contact_phone_number', 'shipper_data');
        $CCSenderEmail = self::getCarrierConfig('email', 'shipper_data');

        $cc->addChild('CCNumofItems', 1);
        $cc->addChild('CCSTypeCode', self::getCarrierConfig('type_code', 'information'));
        $cc->addChild('CCSenderName', $ccSenderName);
        $cc->addChild('CCSenderAdd1', $ccSenderAdd1);
        $cc->addChild('CCSenderAdd2', $ccSenderAdd2);
        $cc->addChild('CCSenderAdd3', $ccSenderAdd3);
        $cc->addChild('CCSenderLocCode', $CCSenderLocCode);
        $cc->addChild('CCSenderLocName',$CCSenderLocName);
        $cc->addChild('CCSenderLocState', $CCSenderLocState);
        $cc->addChild('CCSenderLocPostcode', $CCSenderLocPostcode);
        $cc->addChild('CCSenderLocCtryCode ', $CCSenderLocCtryCode);
        $cc->addChild('CCSenderContact', $CCSenderContact);
        $cc->addChild('CCSenderPhone', $CCSenderPhone);
        $cc->addChild('CCSenderEmail', $CCSenderEmail);

        $address = $this->prepareAddress($request);
        $cc->addChild('CCReceiverName', $request->getRecipientContactCompanyName());
        $cc->addChild('CCReceiverContact', $request->getRecipientContactPersonName());
        $cc->addChild('CCReceiverAdd1', $address[0]);
        $cc->addChild('CCReceiverAdd2', $address[1]);
        $cc->addChild('CCReceiverAdd3', $address[2]);
        $cc->addChild('CCReceiverLocName1', $request->getRecipientAddressCity());
        $cc->addChild('CCReceiverLocCode', null);
        $cc->addChild('CCReceiverLocState', $request->getRecipientAddressCity());
        $cc->addChild('CCReceiverLocPostcode', $request->getRecipientAddressPostalCode());
        $cc->addChild('CCReceiverLocCtryCode', $recipientCountry);
        $cc->addChild('CCReceiverPhone', $request->getRecipientContactPhoneNumber());
        $cc->addChild('CCReceiverEmail', null);
        $cc->addChild('CCWeight', $dimensions['weight']);
        $cc->addChild('CCSenderRef1', $incrementId);
        $cc->addChild('CCSenderRef2', null);
        $cc->addChild('CCSenderRef3', null);

        // Order amount only in international shipping, no local
        if ($request->getShipperAddressCountryCode() == $recipientCountryCode) {
            $cc->addChild('CCCustomsValue', null);
            $cc->addChild('CCCustomsCurrencyCode', null);
        } else {
            $total = Mage::helper('rounding')->process($targetCurrency, $order->getGrandTotal());
            $total = sprintf("%01.2f", $total);
            $cc->addChild('CCCustomsValue', $total);
            $cc->addChild('CCCustomsCurrencyCode', $order->getOrderCurrencyCode());
        }

        $cc->addChild('CCClearanceRef', null);
        $cc->addChild('CCCubicLength', $dimensions['length']);
        $cc->addChild('CCCubicWidth', $dimensions['width']);
        $cc->addChild('CCCubicHeight', $dimensions['height']);

        if ($dimensions['weight_units'] == 'KILOGRAM') {
            $cc->addChild('CCCubicMeasure', 'Kgs');
        } else if ($dimensions['weight_units'] == 'POUND') {
            $cc->addChild('CCCubicMeasure', 'Lbs');
        }

        $codAmount = null;
        $codInstructions = null;
        $codCurrency = null;
        if ($order->getPayment()->getMethodInstance()->getCode() == 'cashondelivery') {
            $codAmount = $order->getGrandTotal();
            $codCurrency = $order->getOrderCurrencyCode();
            $codInstructions = self::getCarrierConfig('delivery_instructions', 'information');
        }

        $cc->addChild('CCCODAmount', $codAmount);
        $cc->addChild('CCCODCurrCode', $codCurrency);
        $cc->addChild('CCDeliveryInstructions', $codInstructions);
        $cc->addChild('CCBag ', 1);
        $cc->addChild('CCNotes', null);
        $cc->addChild('CCSystemNotes', null);
        $cc->addChild('CCOriginLocCode', null);
        $cc->addChild('CCBagNumber', null);
        $cc->addChild('CCCubicWeight', null);
        $cc->addChild('CCDeadWeight', null);
        $cc->addChild('CCGoodsDesc', self::getCarrierConfig('goods_description', 'information'));
        $cc->addChild('CCSenderFax', null);
        $cc->addChild('CCReceiverFax', null);
        $cc->addChild('CCGoodsOriginCtryCode', null);
        $cc->addChild('CCReasonExport', null);
        $cc->addChild('CCShipTerms', null);
        $cc->addChild('CCDestTaxes', null);
        $cc->addChild('CCManNoOfShipments', null);
        $cc->addChild('CCSecurity', null);
        $cc->addChild('CCInsurance', null);
        $cc->addChild('CCInsuranceCurrCode', null);
        $cc->addChild('CCSerialNo', null);
        $cc->addChild('CCReceiverPhone2', null);
        $cc->addChild('CCCreateJob', 0);
        $cc->addChild('CCSurcharge ', null);

        $service = new Zend_Soap_Client('https://ws05.ffdx.net/getshipping_ws/v8/service_getshipping.asmx?WSDL', array(
            'cache_wsdl' => WSDL_CACHE_NONE,
            'encoding'   => 'UTF-8'
        ));
        $serviceResponse = $service->UploadCMawbWithLabelToServer(array(
            'xmlStream' => $xml->asXML(),
        ));

        try {
            $xml = simplexml_load_string($serviceResponse->UploadCMawbWithLabelToServerResult);
        } catch (Exception $e) {
            Mage::throwException('Error whilst receiving response from Carrier: ' . $serviceResponse->UploadCMawbWithLabelToServerResult);
        }

        $json = json_encode($xml);
        $array = json_decode($json, true);
        $connote = $array['Status']['CC']['CCConnote'];
        $accessId = self::getCarrierConfig('access_id');
        $printout = $this->retrievePrintout("http://ws01.ffdx.net/v4/printdoc/docConnoteStyle1.aspx?accessid={$accessId}&shipno={$connote}&format=pdf");

        $response = new Varien_Object(array(
            'info' => array(array(
                'tracking_number' => $connote,
                'label_content'   => $printout
            ))
        ));
        
        return $response;
    }

    /**
     * @param $request
     * @return array
     */
    protected function prepareAddress($request)
    {
        $cut = null;
        $line = array();
        $line[] = htmlentities((string)$request->getRecipientAddressStreet1());
        $line[] = htmlentities((string)$request->getRecipientAddressStreet2());
        $line[] = htmlentities((string)$request->getRecipientAddressStreet3());

        for ($i=0; $i<count($line); $i++) {
            $current = $line[$i];
            if (!is_null($cut)) {
                $current = $cut . ' ' . $current;
                $cut = null;
            }
            if (strlen($current) > self::MAX_ENTRY_LENGTH) {
                $line[$i] = substr($current, 0, self::MAX_ENTRY_LENGTH);
                $cut = substr($current, self::MAX_ENTRY_LENGTH);
            } else {
                $line[$i] = $current;
            }
        }

        return $line;
    }
}
