<?php
/*
 * Class FFDX_ShippingBox_Helper_Data
 */
class FFDX_ShippingBox_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @return DateTime|string
     */
    public function getNow()
    {
        $timeZone = new DateTimeZone(Mage::getStoreConfig('general/locale/timezone'));
        $currentDate = new DateTime("now", $timeZone);
        $currentDate = $currentDate->format('Y-m-d H:i:s');

        return $currentDate;
    }

    /**
     * Connect to API FFDX and response data about track
     *
     * @param $trackingNr
     * @return bool|mixed
     */
    public function getDataFromApi($trackingNr)
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><WSGET />');
        $currentDate = Mage::helper('ffdxshippingbox')->getNow();
        $accessRequest = $xml->addChild('AccessRequest');
        $accessRequest->addChild('WSVersion', 'WS1.0');
        $accessRequest->addChild('FileType', '2');
        $accessRequest->addChild('Action', 'Download');
        $accessRequest->addChild('EntityID', Mage::getStoreConfig('ffdxshipping/general/entity_id'));
        $accessRequest->addChild('EntityPIN', Mage::getStoreConfig('ffdxshipping/general/entity_pin'));
        $accessRequest->addChild('MessageID', Mage::getStoreConfig('ffdxshipping/general/message_id'));
        $accessRequest->addChild('AccessID', Mage::getStoreConfig('ffdxshipping/general/access_id'));
        $accessRequest->addChild('AccessPIN', Mage::getStoreConfig('ffdxshipping/general/access_pin'));
        $accessRequest->addChild('CreatedDateTime', $currentDate);
        $xml->addChild('ReferenceNumber', $trackingNr);

        $service = new Zend_Soap_Client('http://ws05.ffdx.net/ffdx_ws/Service.asmx?WSDL', array(
            'cache_wsdl' => WSDL_CACHE_NONE,
            'encoding'   => 'UTF-8'
        ));

        $serviceResponse = $service->WSDataTransfer(array(
            'xmlStream' => $xml->asXML(),
        ));

        if (!strpos($serviceResponse->WSDataTransferResult, 'No record to download.')) {
            $xml = simplexml_load_string($serviceResponse->WSDataTransferResult);
            $json = json_encode($xml);
            $arrayOfTracks = json_decode($json, true);
            if ($arrayOfTracks != false) {
                array_shift($arrayOfTracks);
                return $arrayOfTracks;
            } else {
                return false;
            }
        }           
    }

    /**
     * Shipping Column involved in Adminhtml Orders Grid
     * @return array
     */
    public function getTrackingColumnConfig()
    {
        $config = array(
            'index' => 'tracking_number',
            'type'  => 'text',
            'header'=> $this->__('Tracking'),
            'width' => '170px',
            'sortable' => false,
            'filter_condition_callback' => array(Mage::getModel('ffdxshippingbox/source_grid_column_filter'), 'loadTrackingsByNumber'),
            'renderer' => 'ffdxshippingbox/adminhtml_sales_order_grid_column_renderer_number'
        );

        return $config;
    }

    /**
     * Tracking status config
     * @return array
     */
    public function getTrackingColumnStatus()
    {
        $config = array(
            'index'     => 'checked',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('catalog')->__('Yes'),
                '0' => Mage::helper('catalog')->__('No'),
            ),
            'align' => 'center',
            'header'    => $this->__('Checked'),
            'width'     => '30px',
            'sortable'  => false,
            'filter_condition_callback' => array(Mage::getModel('ffdxshippingbox/source_grid_column_filter'), 'loadTrackingsByStatus'),
            'renderer'  => 'ffdxshippingbox/adminhtml_sales_order_grid_column_renderer_status',
        );

        return $config;
    }
}
