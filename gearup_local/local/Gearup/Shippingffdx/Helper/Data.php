<?php
/*
 * Class Gearup_Shippingffdx override FFDX_ShippingBox_Helper_Data
 */

class Gearup_Shippingffdx_Helper_Data extends FFDX_ShippingBox_Helper_Data {

    const FFDX_TYPE_REGULAR = '2';
    const FFDX_TYPE_SDS = '1';
    const FRIDAY_CLOSE = 'fri';
    const COURIER_BILL = '19.8';
    const SYSTEM_BILL = '19';
    const SHIPPING_AVIL_1 = array('UAE', 'BHR', 'JOR', 'KWT', 'LBN', 'OMN', 'QAT', 'KSA', 'UGA','TUR', 'TUN', 'TZA', 'SDN');
    const SHIPPING_AVIL_2 = array('PHL', 'PAK', 'NGA', 'MAR', 'KEN', 'IND', 'ETH', 'EGY', 'BGD', 'AGO', 'DZA', 'LKA', 'ZAF');

    public function getDataFromApi($trackingNr) {

      
        $url = Mage::getStoreConfig('ffdxshipping/general/sandbox') ? 'postaweb_sandbox_url' : 'postaweb_url';
        $client = new SoapClient(Mage::getStoreConfig('ffdxshipping/general/' . $url), array('trace' => 1, 'exceptions' => 1));

        if (Mage::getSingleton('core/session')->getShippingffdx()) {
            $type = Mage::getSingleton('core/session')->getShippingffdx();
        } else {
            $search = Mage::getModel('gearup_shippingffdx/tracktype')->getShippingType($trackingNr);
            if ($search->getShippingffdxId()) {
                $type = $search->getType();
            } else {
                $type = self::FFDX_TYPE_REGULAR;
            }
        }           
        if ($type == self::FFDX_TYPE_SDS) {
            $tracking_info = $client->Shipment_Tracking(array(
                'UserName' => Mage::getStoreConfig('ffdxshipping/sds_account/username'),
                'Password' => Mage::getStoreConfig('ffdxshipping/sds_account/password'),
                'ShipperAccount' => Mage::getStoreConfig('ffdxshipping/sds_account/shipper_account'),
                'AirwaybillNumber' => $trackingNr,
                'Reference1' => '',
                'Reference2' => ''
            ));
        } else if ($type == self::FFDX_TYPE_REGULAR) {
            $tracking_info = $client->Shipment_Tracking(array(
                'UserName' => Mage::getStoreConfig('ffdxshipping/alternate_account/username'),
                'Password' => Mage::getStoreConfig('ffdxshipping/alternate_account/password'),
                'ShipperAccount' => Mage::getStoreConfig('ffdxshipping/alternate_account/shipper_account'),
                'AirwaybillNumber' => $trackingNr,
                'Reference1' => '',
                'Reference2' => ''
            ));
        }
      
        if (Mage::getSingleton('core/session')->getShippingffdx()) {
            Mage::getModel('gearup_shippingffdx/tracktype')->saveShippingType($trackingNr, Mage::getSingleton('core/session')->getShippingffdx());
        }
        
        return (array) $tracking_info->Shipment_TrackingResult->TRACKSHIPMENT;
    }

    public function getDataFromApiOld($trackingNr) {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><WSGET />');

        $currentDate = Mage::helper('ffdxshippingbox')->getNow();

        if (Mage::getSingleton('core/session')->getShippingffdx()) {
            $type = Mage::getSingleton('core/session')->getShippingffdx();
        } else {
            $search = Mage::getModel('gearup_shippingffdx/tracktype')->getShippingType($trackingNr);
            if ($search->getShippingffdxId()) {
                $type = $search->getType();
            } else {
                $type = self::FFDX_TYPE_REGULAR;
            }
        }

        $accessRequest = $xml->addChild('AccessRequest');
        $accessRequest->addChild('WSVersion', 'WS1.0');
        $accessRequest->addChild('FileType', '2');
        $accessRequest->addChild('Action', 'Download');
        if ($type == self::FFDX_TYPE_SDS) {
            $accessRequest->addChild('EntityID', Mage::getStoreConfig('ffdxshipping/general/entity_id'));
            $accessRequest->addChild('EntityPIN', Mage::getStoreConfig('ffdxshipping/general/entity_pin'));
            $accessRequest->addChild('MessageID', Mage::getStoreConfig('ffdxshipping/general/message_id'));
            $accessRequest->addChild('AccessID', Mage::getStoreConfig('ffdxshipping/general/access_id'));
            $accessRequest->addChild('AccessPIN', Mage::getStoreConfig('ffdxshipping/general/access_pin'));
        } else if ($type == self::FFDX_TYPE_REGULAR) {
            $accessRequest->addChild('EntityID', Mage::getStoreConfig('ffdxshipping/altaccount/entity_id'));
            $accessRequest->addChild('EntityPIN', Mage::getStoreConfig('ffdxshipping/altaccount/entity_pin'));
            $accessRequest->addChild('MessageID', Mage::getStoreConfig('ffdxshipping/altaccount/message_id'));
            $accessRequest->addChild('AccessID', Mage::getStoreConfig('ffdxshipping/altaccount/access_id'));
            $accessRequest->addChild('AccessPIN', Mage::getStoreConfig('ffdxshipping/altaccount/access_pin'));
            $accessRequest->addChild('CCAccCardCode', Mage::getStoreConfig('ffdxshipping/altaccount/acc_card_code'));
        }
        if (Mage::getSingleton('core/session')->getShippingffdx()) {
            Mage::getModel('gearup_shippingffdx/tracktype')->saveShippingType($trackingNr, Mage::getSingleton('core/session')->getShippingffdx());
        }
        Mage::getSingleton('core/session')->unsShippingffdx();
        $accessRequest->addChild('CreatedDateTime', $currentDate);
        $xml->addChild('ReferenceNumber', $trackingNr);
        $xml->addChild('ShowAltRef', 'Y');

        $service = new Zend_Soap_Client('http://ws05.ffdx.net/ffdx_ws/v12/Service.asmx?WSDL', array(
            'cache_wsdl' => WSDL_CACHE_NONE,
            'encoding' => 'UTF-8'
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

    public function getTrackingRef($trackingNr) {
        $model = Mage::getModel('gearup_shippingffdx/tracktype')->getCollection();
        $model->addFieldToFilter('tracking_number', array('eq' => $trackingNr));
       
        if (!count($model)) {
            return '';
        }
        $search = $model->getFirstItem();
        return $search;
    }

    public function getDestination($order) {
        $country = Mage::getModel('directory/country')->loadByCode($order->getShippingAddress()->getCountryId());
        return $country->getName();
    }

    public function getLastLocation($track) {
        $history = Mage::getModel('ffdxshippingbox/history')->getCollection();
        $history->addFieldToFilter('tracking_id', array('eq' => $track));
        $history->addFieldToFilter('location', array('neq' => 'Unknown'));
        $history->setOrder('history_id', 'DESC');

        $track = $history->getFirstItem();
        return $track->getLocation();
    }

    public function getEstDate($timestamp) {
        $dtime = date('Y-m-D H:i:s', strtotime($timestamp));
        $datetime = explode(' ', $dtime);
        $date = explode('-', $datetime[0]);
        $time = explode(':', $datetime[1]);
        if (strtolower($date[2]) == self::FRIDAY_CLOSE) {
            return date('Y-m-d H:i:s', strtotime($timestamp) + (86400));
        }

        if ($time[0] >= '20') {
            return date('Y-m-d H:i:s', strtotime($timestamp) + (86400));
        } else {
            return date('Y-m-d H:i:s', strtotime($timestamp));
        }
    }

    public function getShippinglist($from, $to, $desti) {
        $from = base64_decode($from);
        $to = base64_decode($to);
        $desti = base64_decode($desti);
        $collection = Mage::getModel('ffdxshippingbox/tracking')->getCollection();
        $collection->getSelect()->join('sales_flat_order_address', 'main_table.order_id = sales_flat_order_address.parent_id',array('country_id' ) )->where("sales_flat_order_address.address_type =  'shipping'");
        $collection->addFieldToFilter('main_table.created_at', array('gt'=>$from));
        $collection->addFieldToFilter('main_table.created_at', array('lt'=>$to));
        if ($desti == 'dosmetic') {
            $collection->addFieldToFilter('sales_flat_order_address.country_id', array('eq'=>'AE'));
        } else if ($desti == 'inter') {
            $collection->addFieldToFilter('sales_flat_order_address.country_id', array('neq'=>'AE'));
        }
        $collection->setOrder('main_table.created_at', 'ASC');

        return $collection;
    }

    public function matchShipping($track, $order, $filedata) {
        $search = array_search($track, array_column($filedata, 'AWB'));
        $searchOrder = array_search($order, array_column($filedata, 'Ref'));
        if ($search !== false && $searchOrder !== false) {
            return $filedata[$search];
        } else {
            return Gearup_Autoinvoice_Helper_Data::NOT_FOUND;
        }
    }

    public function getShippingTrack($trackId) {
        $shipping = Mage::getModel('ffdxshippingbox/tracking')->load($trackId);

        return $shipping;
    }

    public function recordHistory($invoice, $action) {
        $history = Mage::getModel('gearup_shippingffdx/history');
        $admin = Mage::getSingleton('admin/session')->getUser();

        $history->setTrackId($invoice);
        $history->setActions($action);
        $history->setCreateDate(Mage::getModel('core/date')->date('Y-m-d H:i:s'));
        $history->setRecordBy($admin->getFirstname());
        $history->save();
    }

    public function getTrackChecked($trackId) {
        $trackM = Mage::getModel('ffdxshippingbox/tracking')->load($trackId);
        if ($trackM->getChecked()) {
            return array('status' => 'Yes', 'bool' => 1);
        } else {
            return array('status' => 'No', 'bool' => 0);
        }
    }

    public function getDestcourier($t,$i=0) {
        $m = Mage::getModel("gearup_shippingffdx/destination")->getCollection();
        $m->addFieldToFilter('destination_id', array('eq'=>$t));
        if ($m->getSize()) {
            if($i){
                $destination = $m->getFirstItem();
                return $destination->getCourierNickname();
            }
            return $destination = $m->getFirstItem();
        }
        return '';
    }
}
