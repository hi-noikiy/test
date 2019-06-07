<?php

class FFDX_ShippingBox_Model_History extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('ffdxshippingbox/history');
    }

    public function getHistoryGridCollection($tracking)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('tracking_id', $tracking->getId())
            ->setOrder('created_at', 'DESC');

        $source = Mage::getModel('ffdxshippingbox/source_event_code');
        $eventCodes = $source->getMap();

        /*foreach ($collection as $event) {
            $eventTime = $event->getCreatedAt();
            $eventTime = new DateTime($eventTime);
            $eventTime = $eventTime->format('H:i - d M Y');
            $event->setCreatedAt($eventTime);
            if (!array_key_exists($event->getActivity(), $eventCodes)) {
                $event->setActivity('0');
                $event->save();
            }
        }*/

        return $collection;
    }
}