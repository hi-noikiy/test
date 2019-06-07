<?php

/**
 * Class FFDX_ShippingBox_Model_Observer
 */
class FFDX_ShippingBox_Model_Observer
{
    const STATUS_DELIVERED = 1;
    const STATUS_UNCHECKED = 0;
    /**
     * @param Varien_Event_Observer $observer
     */
    public function addInfoAboutShipping(Varien_Event_Observer $observer)
    {
        $track = $observer->getTrack();
        $tracking = $this->initTracking($track);
        $this->checkTrack($tracking);
    }

    /**
     * @param $track
     * @return mixed
     */
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
                ->save();
        }

        return $tracking;
    }

    /**
     * Remove row from tables after action delete track
     * @param Varien_Event_Observer $observer
     */
    public function afterDeleteOfTrack(Varien_Event_Observer $observer)
    {
        $track = $observer->getTrack();

        $shipmentId = $track->getParentId();
        $orderId = $track->getOrderId();
        $trackNumber = $track->getTrackNumber();

        $currentTracking = Mage::getModel('ffdxshippingbox/tracking')->getCollection()
            ->addFieldTofilter('order_id', $orderId)
            ->addFieldToFilter('shipment_id', $shipmentId)
            ->addFieldTofilter('tracking_number', $trackNumber)
            ->load();

        foreach ($currentTracking as $item) {
            $trackingToDelete = $item->getTrackingId();

            Mage::getModel('ffdxshippingbox/tracking')
                ->load($trackingToDelete)
                ->delete();

            $currentHistory = Mage::getModel('ffdxshippingbox/history')->getCollection()
                ->addFieldToFilter('tracking_id', $trackingToDelete )
                ->load();

            foreach ($currentHistory as $history) {
                $historyToDelete = $history->getTrackingId();

                Mage::getModel('ffdxshippingbox/history')
                    ->load($historyToDelete, 'tracking_id')
                    ->delete();
            }
        }
    }

    /**
     * save tracks checked by API depends on shape of data comes form API
     * @param $checkedTracks
     */
    public function saveTracking($checkedTracks,$trackno)
    {
        if ($checkedTracks) {
            if(isset($checkedTracks['DateTime']))
                $events[] = $checkedTracks; 
            else
                $events = $checkedTracks;            
            try {                                   
                   $this->save2($events,$trackno); 
            }catch (Exception $e) {
               Mage::log('Events: ' . $e->getMessage(),null, 'ffdxshippingbox_save_tracking.log');
            } 
        }
    }
    
    public function saveTrackingOld($checkedTracks)
    {
        if (isset($checkedTracks['Events'])) {
         $events = $checkedTracks['Events'];
             foreach ($events as $event) {
                 try {
                     $this->save($event);
                 } catch (Exception $e) {
                     Mage::log('Events: ' . $e->getMessage(),null, 'ffdxshippingbox_save_tracking.log');
                 }
             }
        } elseif (isset($checkedTracks['Event'])) {
            $event = $checkedTracks['Event'];
            try {$this->save($event);} 
            catch (Exception $e) {
                Mage::log('One Event: ' . $e->getMessage(),null, 'ffdxshippingbox_save_tracking.log');
            }
        }
    }

    public function save2($event,$trackno)
    {
        foreach ($event as $node) {
            $node = (array)$node;
            $event = $node['DateTime'];
            $location = [];//$node['UpdateEntityLocationName'];
            if (is_array($location)) {
                $location = 'Unknown';
            }
            $eventTime = date('Y-m-d H:i:s', strtotime(str_replace("/", "-", $node['DateTime'])));
            $collectionTracks = Mage::getResourceModel('ffdxshippingbox/tracking_collection')
                ->addFieldToFilter('tracking_number', $trackno);
            //Mage::log('tracks = '.$collectionTracks->getSize(), null, 'checkffdxcreate.log');
            foreach ($collectionTracks as $track) {
                $trackingId = $track->getTrackingId();
                    $history = Mage::getResourceModel('ffdxshippingbox/history_collection')
                        ->loadHistory($trackingId, $node['Event'], $location, $eventTime);
                    if (!$history->getSize()) {
                        Mage::getModel('ffdxshippingbox/history')
                            ->setTrackingId($trackingId)
                            ->setEvent($node['Event'])                         
                            ->setCreatedAt($eventTime)
                            ->save();
                    }
                    if($node['Event'] == "DELIVERED")                    
                        Mage::getModel('gearup_shippingffdx/tracktype')->changeOrderStatus($track->getOrderId());              
            }
        }
    }
    
    
    public function saveOld($event)
    {
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
                    $history = Mage::getResourceModel('ffdxshippingbox/history_collection')
                        ->loadHistory($trackingId, $eventId, $location, $eventTime);

                    if (!$history->getSize()) {
                        Mage::getModel('ffdxshippingbox/history')
                            ->setTrackingId($trackingId)
                            ->setActivity($eventId)
                            ->setLocation($location)
                            ->setCreatedAt($eventTime)
                            ->save();
                        //Mage::log('create = track_id:'.$trackingId.', event:'.$eventId, null, 'checkffdxcreate.log');
                    } else {
                        Mage::getModel('ffdxshippingbox/history')
                            ->load($trackingId, 'tracking_id')
                            ->setActivity($eventId)
                            ->setLocation($location)
                            ->save();
                        $history = Mage::getModel('ffdxshippingbox/history')->load($trackingId, 'tracking_id');
                        //Mage::log('update = track_id:'.$trackingId.', event:'.$eventId.', history:'.$history->getHistoryId(), null, 'checkffdxcreate.log');
                    }
                }
            }
        }
    }

    public function checkAll()
    {
        $tracksCollection = Mage::getModel('ffdxshippingbox/tracking')->getCollection()->getUncheckedTracks();
                     
        foreach ($tracksCollection as $tracking) {
            try{
                    $this->checkTrack($tracking);
            } catch (Exception $e) {
                    Mage::log('ERROR in ffdxshippingbox cron job with tracking ' . $tracking->getTrackingNumber() . ': ' . $e->getMessage(),null, 'ffdxshippingbox_cron.log');
            }
        }
    }

    /**
     * Check track
     */
    public function checkTrack($tracking)
    {
        $history = Mage::getModel('ffdxshippingbox/history')->getCollection()
                ->addFieldTofilter('tracking_id', $tracking->getTrackingId())
                ->addFieldTofilter('event', 'DELIVERED')
                ->getFirstItem();
        $orderStatus = Mage::getModel('sales/order')->load($tracking->getData('order_id'))->getStatus();

        if ($history->getEvent() != 'DELIVERED'  && !in_array($orderStatus, array('closed', 'canceled'))){
            try {
                $shipmentcoll =  Mage::getModel('sales/order')->load($tracking->getData('order_id'))->getTracksCollection();
                foreach ($shipmentcoll as $ship) {
                    if($ship->getTrack_number() == $tracking->getTrackingNumber() && ($ship->getTitle() == 5 || $ship->getTitle() == 'SpeedEx')) {
                        $checkedTracks = Mage::helper('ffdxshippingbox')->getDataFromApi($tracking->getTrackingNumber());
                        if(!isset($checkedTracks['ErrorMsg'])){
                            $this->saveTracking($checkedTracks, $tracking->getTrackingNumber());
                            //Mage::log($tracking->getTrackingNumber().' = '.count($checkedTracks), null, 'checkffdxcreate.log');
                            //Mage::log('==================================================================', null, 'checkffdxcreate.log');
                        }
                    }
                }
            } catch (Exception $e) {
                Mage::log('Undelivered Tracking Saving: ' . $tracking->getTrackingNumber() . ': ' . $e->getMessage(),null, 'ffdxshippingbox_save_tracking.log');
            }
        } else {
            $trackingModel = Mage::getModel('ffdxshippingbox/tracking')->load($tracking->getTrackingNumber(), 'tracking_number');
            try {
                $trackingModel
                    ->setChecked(self::STATUS_DELIVERED)
                    ->save();
            } catch (Exception $e) {
                Mage::log('Delivered Tracking Saving: ' . $tracking->getTrackingNumber() . ': ' . $e->getMessage(),null, 'ffdxshippingbox_save_tracking.log');
            }
        }
    }
}

