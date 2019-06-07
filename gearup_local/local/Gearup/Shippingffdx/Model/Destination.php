<?php

class Gearup_Shippingffdx_Model_Destination extends Mage_Core_Model_Abstract
{
    const SHIP_DOSMATIC = 'dosmetic';
    const SHIP_INTER = 'inter';

    public function _construct(){
        parent::_construct();
        $this->_init("gearup_shippingffdx/destination");
    }

    public function destinationNumber($code,$title){
        $collection = $this->getCollection();
        $collection->addFieldToFilter('code', array('eq'=>$code));
        $collection->addFieldToFilter('courier_name', array('eq'=>$title));
        if ($collection->getSize()) {
            $destination = $collection->getFirstItem();
            return $destination->getNumber();
        } else {
            return '';
        }
    }

    public function TrackingUrl($code,$title){
        $collection = $this->getCollection();
        $collection->addFieldToFilter('code', array('eq'=>$code));
        $collection->addFieldToFilter('courier_name', array('eq'=>$title));
        if ($collection->getSize()) {
            $destination = $collection->getFirstItem();
            return $destination->getTracking_url();
        } else {
            return '';
        }
   }

}
