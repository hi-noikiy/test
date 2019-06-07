<?php
/**
 * Shipping API
 */

ini_set('include_path', ini_get('include_path') . ':' . '/home/zbychu/workspace/gearup/lib');
include_once('Zend/Loader.php');
Zend_Loader::loadClass('Zend_Soap_Client');
Zend_Loader::loadClass('Zend_Soap_Client_Common');


$xmlStream =<<< TEXT
<?xml version="1.0" encoding="ISO-8859-1"?>
<WSGET>
    <AccessRequest>
        <WSVersion>WS1.3</WSVersion>
        <FileType>2</FileType>
        <Action>download</Action>
        <EntityID>WS_ML_AP4615</EntityID>
        <EntityPIN>erbii83</EntityPIN>
        <MessageID>1601286204</MessageID>
        <AccessID>MODLINE_ID</AccessID>
        <AccessPIN>3D07153</AccessPIN>
        <CreatedDateTime></CreatedDateTime>
        <ReferenceNumber>40050006629</ReferenceNumber>
    </AccessRequest>
</WSGET>
TEXT;


try {

    $service = new Zend_Soap_Client('https://ws05.ffdx.net/ffdx_ws/v8/service.asmx?WSDL', array(
        'cache_wsdl' => WSDL_CACHE_NONE,
        'encoding'   => 'UTF-8'
    ));

    $response = $service->WSDataTransfer(array(
        'xmlStream' => $xmlStream,
    ));

    file_put_contents('api-get-response.xml', $response->WSDataTransferResult);

} catch(Exception $e) {
    echo $e->getMessage();
}