<?php

class Gearup_Shippingffdx_Model_Observer extends FFDX_ShippingBox_Model_Observer
{
    public function initTracking($track)
    {
        $trackings = Mage::getModel('ffdxshippingbox/tracking')
            ->getCollection()
            ->addFieldToFilter('shipment_id', $track->getParentId());

        if ($trackings->getSize()) {
            $tracking = $trackings->getFirstItem();
        } else {
            $tracking = Mage::getModel('ffdxshippingbox/tracking')
                ->setOrderId($track->getOrderId())
                ->setShipmentId($track->getParentId())
                ->setTrackingNumber($track->getTrackNumber())
                ->setCreatedAt(Mage::getSingleton('core/date')->gmtDate())
                ->save();
        }

        return $tracking;
    }

    public function saveOld($event)
    {
        $i = 0;
        foreach ($event as $node) {
            $referenceNumber = $node['ReferenceNumber'];
            $eventId = $node['EventID'];
            $location = $node['UpdateEntityLocationName'];
            if (is_array($location)) {
                $location = 'Unknown';
            }
            $eventTime = $node['EventDateTime'];

            $eventTime = new DateTime($eventTime);
            $eventTime = $eventTime->format('Y-m-d H:i:s');

            $collectionTracks = Mage::getResourceModel('ffdxshippingbox/tracking_collection')
                ->addFieldToFilter('tracking_number', $referenceNumber);
            //Mage::log('save tracks = '.$collectionTracks->getSize(), null, 'checkffdxcreate.log');
            foreach ($collectionTracks as $track) {
                $trackingId = $track->getTrackingId();

                if (self::STATUS_DELIVERED == $eventId) {
                    Mage::getModel('ffdxshippingbox/tracking')
                        ->load($trackingId)
                        ->setChecked(self::STATUS_DELIVERED)
                        ->save();

                    $history = Mage::getResourceModel('ffdxshippingbox/history_collection')
                        ->loadHistory($trackingId, $eventId, $location, $eventTime);
                    if (!$history->getSize()) {
                        Mage::getModel('ffdxshippingbox/history')
                            ->setTrackingId($trackingId)
                            ->setActivity($eventId)
                            ->setLocation($location)
                            ->setCreatedAt($eventTime)
                            ->save();
                    }
                    Mage::getModel('gearup_shippingffdx/tracktype')->changeOrderStatus($track->getOrderId());
                } else {
                    if (!$i) {
                        Mage::getModel('gearup_shippingffdx/tracktype')->deletePreviousH($trackingId);
                        Mage::log('delete histories of track id = '.$trackingId, null, 'ffdxhistory.log');
                    }
                    Mage::getModel('ffdxshippingbox/history')
                        ->setTrackingId($trackingId)
                        ->setActivity($eventId)
                        ->setLocation($location)
                        ->setCreatedAt($eventTime)
                        ->save();
                    //Mage::log('create = track_id:'.$trackingId.', event:'.$eventId, null, 'checkffdxcreate.log');
                }
//                if ($node['AlternateReference'] && $node['OriginEntityID'] != $node['UpdateEntityID']) {
//                    Mage::getModel('gearup_shippingffdx/tracktype')->updateRefTrack($node);
//                }
            }
            $i++;
        }
    }
    
    
    public function save($event,$trackno)
    {
        foreach ($event as $node) {
            $event = $node->DateTime;
            $location = [];
            if (is_array($location)) {
                $location = 'Unknown';
            }
            $eventTime = $node->DateTime;
            $eventTime = new DateTime($eventTime);
            $eventTime = $eventTime->format('Y-m-d H:i:s');
            $collectionTracks = Mage::getResourceModel('ffdxshippingbox/tracking_collection')
                ->addFieldToFilter('tracking_number', $trackno);
            //Mage::log('tracks = '.$collectionTracks->getSize(), null, 'checkffdxcreate.log');
            foreach ($collectionTracks as $track) {
                $trackingId = $track->getTrackingId();

                    $history = Mage::getResourceModel('ffdxshippingbox/history_collection')
                        ->loadHistory($trackingId, $node->Event, $location, $eventTime);
                    if (!$history->getSize()) {
                        Mage::getModel('ffdxshippingbox/history')
                            ->setTrackingId($trackingId)
                            ->setEvent($node->Event)
                          //  ->setLocation($location)
                            ->setCreatedAt($eventTime)
                            ->save();
                    }
                    if($node->Event == "DELIVERED")
                        Mage::getModel('gearup_shippingffdx/tracktype')->changeOrderStatus($track->getOrderId());              
            }
        }
    }
    
    function beforeHtml($observer){
        $block = $observer->getBlock();
        if ($block instanceof Mage_Adminhtml_Block_Sales_Shipment_Grid) {
                 $block->getMassactionBlock()->addItem('export_to_csv', array(
                     'label' => Mage::helper('sales')->__('Export to CSV'),
                     'url' => Mage::helper('adminhtml')->getUrl('*/shippingffdx/exportToCSV'),
                 ));
         }
    }
}

