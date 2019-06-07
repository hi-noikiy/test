<?php
/**
 * Shipping API
 */

ini_set('include_path', realpath(dirname(__FILE__) . '/../../../../../..') . '/lib');
include_once('Zend/Loader.php');
Zend_Loader::loadClass('Zend_Soap_Client');
Zend_Loader::loadClass('Zend_Soap_Client_Common');


$xmlPut =<<< TEXT
<?xml version="1.0" encoding="ISO-8859-1"?>
<WSGET>
    <AccessRequest>
        <WSVersion>WS1.3</WSVersion>
        <FileType>19</FileType>
        <Action>upload</Action>
        <EntityID>WS_ML_AP4615</EntityID>
        <EntityPIN>erbii83</EntityPIN>
        <MessageID>1601286204</MessageID>
        <AccessID>MODLINE_ID</AccessID>
        <AccessPIN>3D07153</AccessPIN>
        <CreatedDateTime/>
    </AccessRequest>
	<CMDetail>
		<CC>
			<CCIsValidate>N</CCIsValidate>
			<CCLabelReq>Y</CCLabelReq>
			<CCAccCardCode>ABC111</CCAccCardCode>
			<CCCustDeclaredWeight>2</CCCustDeclaredWeight>
			<CCWeightMeasure>Kgs</CCWeightMeasure>
			<CCNumofItems>2</CCNumofItems>
			<CCSTypeCode>EN</CCSTypeCode>
			<CCSenderName></CCSenderName>
			<CCSenderAdd1>Washington St. 31</CCSenderAdd1>
			<CCSenderAdd2/>
			<CCSenderAdd3/>
			<CCSenderLocCode>SG</CCSenderLocCode>
			<CCSenderLocName>Dubai</CCSenderLocName>
			<CCSenderLocState>Dubai</CCSenderLocState>
			<CCSenderLocPostcode>192221</CCSenderLocPostcode>
			<CCSenderLocCtryCode>AE</CCSenderLocCtryCode>
			<CCSenderContact>Mr Wayne</CCSenderContact>
			<CCSenderPhone>911122110</CCSenderPhone>
			<CCSenderEmail>wayne.test@test.com</CCSenderEmail>
			<CCReceiverName>said</CCReceiverName>
			<CCReceiverAdd1>amman</CCReceiverAdd1>
			<CCReceiverAdd2>test receiving address 2</CCReceiverAdd2>
			<CCReceiverAdd3>address 3</CCReceiverAdd3>
			<CCReceiverLocCode>AE</CCReceiverLocCode>
			<CCReceiverLocName>Dubai</CCReceiverLocName>
			<CCReceiverLocState>Dubai</CCReceiverLocState>
			<CCReceiverLocPostcode>111221</CCReceiverLocPostcode>
			<CCReceiverLocCtryCode>AE</CCReceiverLocCtryCode>
			<CCReceiverContact>Test</CCReceiverContact>
			<CCReceiverPhone>092652323787980</CCReceiverPhone>
			<CCReceiverEmail>test@test.com</CCReceiverEmail>
			<CCWeight>150</CCWeight>
			<CCSenderRef1>100000948</CCSenderRef1>
			<CCSenderRef2/>
			<CCSenderRef3/>
			<CCCustomsValue>120</CCCustomsValue>
			<CCCustomsCurrencyCode>SGD</CCCustomsCurrencyCode>
			<CCClearanceRef/>
			<CCCubicLength>10</CCCubicLength>
			<CCCubicWidth>10</CCCubicWidth>
			<CCCubicHeight>10</CCCubicHeight>
			<CCCubicMeasure>Kgs</CCCubicMeasure>
			<CCCODAmount>2</CCCODAmount>
			<CCCODCurrCode>AED</CCCODCurrCode>
			<CCBag>1</CCBag>
			<CCNotes/>
			<CCSystemNotes>notes</CCSystemNotes>
			<CCOriginLocCode/>
			<CCBagNumber/>
			<CCCubicWeight/>
			<CCDeadWeight/>
			<CCDeliveryInstructions>2014-01-20</CCDeliveryInstructions>
			<CCGoodsDesc>10</CCGoodsDesc>
			<CCSenderFax/>
			<CCReceiverFax/>
			<CCGoodsOriginCtryCode/>
			<CCReasonExport/>
			<CCShipTerms/>
			<CCDestTaxes/>
			<CCManNoOfShipments/>
			<CCSecurity/>
			<CCInsurance/>
			<CCInsuranceCurrCode/>
			<CCSerialNo/>
			<CCReceiverPhone2>092657873980</CCReceiverPhone2>
			<CCCreateJob>0</CCCreateJob>
			<CCSurcharge/>
		</CC>
	</CMDetail>
</WSGET>
TEXT;


try {

    $service = new Zend_Soap_Client('https://ws05.ffdx.net/getshipping_ws/v8/service_getshipping.asmx?WSDL', array(
        'cache_wsdl' => WSDL_CACHE_NONE,
        'encoding'   => 'UTF-8'
    ));

    $response = $service->UploadCMawbWithLabelToServer(array(
        'xmlStream' => $xmlPut,
    ));

    file_put_contents('api-put-response.xml', $response->UploadCMawbWithLabelToServerResult);

} catch(Exception $e) {
    echo $e->getMessage();
}
