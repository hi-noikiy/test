<?php

class Gearup_Shippingffdx_Model_Carrier extends Gearup_Shippingffdx_Model_Carrier_Amasty_Pure {

    protected function _doShipmentRequest($request, $package) {

        $order = $incrementId = $request->getOrderShipment()->getOrder();
        
        $incrementId = $order->getIncrementId();
        $dimensions = & $package['params'];

        $ccSenderName = self::getCarrierConfig('company_name', 'shipper_data');
        $ccSenderAdd1 = self::getCarrierConfig('contact_name', 'shipper_data');
        $ccSenderAdd2 = self::getCarrierConfig('address1', 'shipper_data');
        $ccSenderAdd3 = self::getCarrierConfig('address2', 'shipper_data');
        $CCSenderLocCode = self::getCarrierConfig('address_country_code', 'shipper_data');
        $CCSenderLocName = self::getCarrierConfig('city', 'shipper_data');
        // $CCSenderLocState = self::getCarrierConfig('address_state_or_province', 'shipper_data');
        $CCSenderLocPostcode = self::getCarrierConfig('address_postal_code', 'shipper_data');

        // $CCSenderContact = self::getCarrierConfig('sender_contact_person', 'shipper_data');
        $CCSenderPhone = str_replace('+', '', self::getCarrierConfig('contact_phone_number', 'shipper_data'));
        $CCSenderEmail = self::getCarrierConfig('email', 'shipper_data');
        $posta_lib_helper = new Posta_Helper();
        $shp = new Posta_ShipmentInfo(); //Shipment info;
        $cc = new Posta_ConnoteContacts(); //ConnoteContacts ;
        $cn = new Posta_ConnoteNotes(); //ConnoteNotes;
        $cr = new Posta_ConnoteRfee(); //ConnoteRfee;
        $con = new Posta_Consignee(); //Consignee;
        $cl = new Posta_Clientinfo(); //Clientinfo;
        $i=0;

       // $order = Mage::getModel('sales/order')->load($incrementId, 'increment_id');
       
        if (Mage::getSingleton('core/session')->getShippingffdx() == Gearup_Shippingffdx_Helper_Data::FFDX_TYPE_SDS) {
            $cl->UserName = self::getCarrierConfig('username', 'sds_account');
            $cl->Password = self::getCarrierConfig('password', 'sds_account');
            $cl->ShipperAccount = self::getCarrierConfig('shipper_account', 'sds_account');
        }else{
            $cl->UserName = self::getCarrierConfig('username', 'alternate_account');
            $cl->Password = self::getCarrierConfig('password', 'alternate_account');
            $cl->ShipperAccount = self::getCarrierConfig('shipper_account', 'alternate_account');            
        }
        $cl->CodeStation = self::getCarrierConfig('code_station');
        $postaShippingData = Mage::registry('posta_shipping_data');
        $shp->CodeService = $postaShippingData['code_service'];
        $shp->CodeShippmentType = $postaShippingData['shipment_type_code'];
        $shp->ConnoteDescription = $postaShippingData['connot_desc'];
        $shp->ConnoteInsured = $postaShippingData['connot_insured'];
        $shp->ConnotePieces = $postaShippingData['connot_pieces'];
        $shp->ConnoteProhibited = $postaShippingData['connot_prohibited'];
        if($shp->CodeShippmentType == "SHPT2"){        
            foreach ($order->getAllVisibleItems() as $item) {
                 $cpi[$i] = new Posta_ConnotePerformaInvoice();
                 $cpi[$i]->CodeHS = 'HSCD28';
                 $cpi[$i]->OrginCountry = 'CHN';
                 $cpi[$i]->Quantity = (int)$item->getQtyOrdered();
                 $cpi[$i]->RateUnit =  money_format('%i',$item->getPrice());
                 $cpi[$i]->Description = 'SKU-'.$item->getSku();
                 $cpi[$i]->CodePackageType  = "PCKT2";
                 $i++;
            }
            $shp->ConnotePerformaInvoice = $cpi;             
         }

        $cr->Reference1 = $order->getIncrementId();
        $cr->Reference2 = "";

        $con->FromName = $ccSenderName;
        $con->FromAddress = implode(' ', array($ccSenderAdd1, $ccSenderAdd2, $ccSenderAdd3));
        $con->FromCodeCountry = $CCSenderLocCode;
        $con->FromCity = $posta_lib_helper->getCityCode($con->FromCodeCountry, $CCSenderLocName);
        $con->FromArea = "NA";

        $con->FromMobile = $CCSenderPhone;
        $con->FromPinCode = $CCSenderLocPostcode;
        $con->FromProvince = $posta_lib_helper->getProvinceCode($con->FromCodeCountry,self::getCarrierConfig('address_state_or_province', 'shipper_data'));
        $con->ToCodeCountry = $posta_lib_helper->get3DigitCountryCode($request->getRecipientAddressCountryCode());
        $con->ToProvince = $posta_lib_helper->getProvinceCode($con->ToCodeCountry, $request->getRecipientAddressCity());
        if($con->FromProvince == "NA" || $con->ToProvince == "NA"){
        	$con->FromProvince = $con->ToProvince = "NA";
        }
        $con->FromTelphone = $CCSenderPhone;
        $con->Remarks = "";
        $address = $this->prepareAddress($request);
        $con->Company = "NA";//$request->getRecipientContactCompanyName();
        $con->ToName = $request->getRecipientContactPersonName();
        $con->ToAddress = $address[0] . ' ' . $address[1] . ' ' . $address[2].' '.$request->getRecipientAddressCity();
        $con->ToArea = "NA";
        $con->ToCity = $posta_lib_helper->getCityCode($con->ToCodeCountry, $request->getRecipientAddressCity());
        $con->ToCivielID = "";

        $con->ToCodeSector = "NA";
        $con->ToTelPhone = preg_replace('/\D/', '',  $request->getRecipientContactPhoneNumber());
        $con->ToDesignation = "NA";
        $con->ToMobile = preg_replace('/\D/', '',  $request->getRecipientContactPhoneNumber());
        $con->ToPinCode = $request->getRecipientAddressPostalCode();
        

        $shp->CodeCurrency = $order->getOrderCurrencyCode();
        $shp->CostShipment = $order->getShippingAmount();

        $codAmount = null;

        $codCurrency = null;
        if ($order->getPayment()->getMethodInstance()->getCode() == 'cashondelivery') {
            $codAmount = money_format('%i',$order->getGrandTotal());
            $codCurrency = $order->getOrderCurrencyCode();
        }
        $shp->CashOnDelivery = $codAmount;
        $shp->CashOnDeliveryCurrency = $order->getOrderCurrencyCode();
        $shp->NeedRoundTrip = "N";
        //$shp->AppointmentDate = date('Y-m-d');
        //$shp->AppointmentFromTime = "";
        //$shp->AppointmentToTime = "";
        $cc->Email1 = $request->getRecipientEmail();
        $cc->Email2 = "";
        $cc->TelHome = "";
        $cc->TelMobile = preg_replace('/\D/', '', $request->getRecipientContactPhoneNumber());
        $cc->WhatsAppNumber = "";

        $cn->Note1 = "";
        $cn->Note2 = "";
        $cn->Note3 = "";
        $cn->Note4 = "";
        $cn->Note5 = "";
        $cn->Note6 = "";

        try {
            $url = self::getCarrierConfig('sandbox', 'general') ? 'postaweb_sandbox_url' : 'postaweb_url';
            $client = new SoapClient(self::getCarrierConfig($url, 'general'), array('trace' => 1, 'exceptions' => 1));

            $shp->ConnoteContact = $cc;
            $shp->ConnoteNotes = $cn;
            $shp->ConnoteRef = $cr;
            $shp->Consignee = $con;
            $shp->ClientInfo = $cl;

            $id[0] = new Posta_ItemDetails(); //item details;
            $id[0]->ConnoteHeight = $dimensions['height'];
            $id[0]->ConnoteLength = $dimensions['length'];
            $id[0]->ConnoteWeight = number_format($dimensions['weight'], 2, '.', '');
            $id[0]->ConnoteWidth = $dimensions['width'];
            $shp->ItemDetails = $id;

            $shipment_info = $client->Shipment_Creation(array(
                'SHIPINFO' => $shp));
            //Mage::log($shp, null, 'shippingffdx_error.log');

            if (preg_match('/FALSE RESPONSE/', $shipment_info->Shipment_CreationResult))
                Mage::throwException('Error whilst receiving response from Carrier: ' . $shipment_info->Shipment_CreationResult);
            else {
                $response = new Varien_Object(array(
                    'info' => array(array(
                            'tracking_number' => $shipment_info->Shipment_CreationResult,
                            'label_content' => "http://www.postaplus.com/Customer/ShipmentDetails.aspx?sno={$shipment_info->Shipment_CreationResult}&RefNo=".$cr->Reference1
                        ))
                ));
                return $response;
            }
        } catch (SoapFault $fault) {
            Mage::log('Error:' . $fault->faultstring, null, 'shippingffdx_error.log');
            Mage::log($shp, null, 'shippingffdx_error.log');
            Mage::throwException('Error whilst receiving response from Carrier: ' . $fault->faultstring);
        }
    }

/*    protected function _doShipmentRequestOld($request, $package) {
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
        if (Mage::getSingleton('core/session')->getShippingffdx() == Gearup_Shippingffdx_Helper_Data::FFDX_TYPE_SDS) {
            $accessRequest->addChild('EntityID', self::getCarrierConfig('entity_id'));
            $accessRequest->addChild('EntityPIN', self::getCarrierConfig('entity_pin'));
            $accessRequest->addChild('MessageID', self::getCarrierConfig('message_id'));
            $accessRequest->addChild('AccessID', self::getCarrierConfig('access_id'));
            $accessRequest->addChild('AccessPIN', self::getCarrierConfig('access_pin'));
        } else if (Mage::getSingleton('core/session')->getShippingffdx() == Gearup_Shippingffdx_Helper_Data::FFDX_TYPE_REGULAR) {
            $accessRequest->addChild('EntityID', self::getCarrierConfig('entity_id', 'altaccount'));
            $accessRequest->addChild('EntityPIN', self::getCarrierConfig('entity_pin', 'altaccount'));
            $accessRequest->addChild('MessageID', self::getCarrierConfig('message_id', 'altaccount'));
            $accessRequest->addChild('AccessID', self::getCarrierConfig('access_id', 'altaccount'));
            $accessRequest->addChild('AccessPIN', self::getCarrierConfig('access_pin', 'altaccount'));
        }
        $accessRequest->addChild('CreatedDateTime', $dateTime);

        $cc = $xml->addChild('CMDetail')->addChild('CC');
        $cc->addChild('CCIsValidate', self::getCarrierConfig('is_validate', 'information') ? 'Y' : 'N');
        $cc->addChild('CCLabelReq', self::getCarrierConfig('label_required', 'information') ? 'Y' : 'N');
        if (Mage::getSingleton('core/session')->getShippingffdx() == Gearup_Shippingffdx_Helper_Data::FFDX_TYPE_SDS) {
            $cc->addChild('CCAccCardCode', self::getCarrierConfig('acc_card_code', 'information'));
        } else if (Mage::getSingleton('core/session')->getShippingffdx() == Gearup_Shippingffdx_Helper_Data::FFDX_TYPE_REGULAR) {
            $cc->addChild('CCAccCardCode', self::getCarrierConfig('acc_card_code', 'altaccount'));
        }
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
        $CCSenderContact = self::getCarrierConfig('sender_contact_person', 'shipper_data');
        $CCSenderPhone = self::getCarrierConfig('contact_phone_number', 'shipper_data');
        $CCSenderEmail = self::getCarrierConfig('email', 'shipper_data');

        $cc->addChild('CCNumofItems', 1);
        $cc->addChild('CCSTypeCode', self::getCarrierConfig('type_code', 'information'));
        $cc->addChild('CCSenderName', $ccSenderName);
        $cc->addChild('CCSenderAdd1', $ccSenderAdd1);
        $cc->addChild('CCSenderAdd2', $ccSenderAdd2);
        $cc->addChild('CCSenderAdd3', $ccSenderAdd3);
        $cc->addChild('CCSenderLocCode', $CCSenderLocCode);
        $cc->addChild('CCSenderLocName', $CCSenderLocName);
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
            'encoding' => 'UTF-8'
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
                    'label_content' => $printout
                ))
        ));

        return $response;
    }
*/
    public function getTrackingInfo($number){
        $track = Mage::getModel('sales/order_shipment_track')->load($number,'track_number');

        $track->setTracking($track->getData('track_number'));
        $Incrementid = Mage::getModel('sales/order')->load($track->getOrderId())->getIncrementId();
        $track->setUrl('http://www.postaplus.com/Customer/ShipmentDetails.aspx?sno='.$track->getData('track_number').'&RefNo='.$Incrementid);        
        $track->setCarrierTitle($track->getData('title'));
        return $track;
    } 
}
