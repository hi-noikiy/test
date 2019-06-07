<?php

class FFDX_ShippingBox_Model_Resource_Tracking extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('ffdxshippingbox/tracking', 'tracking_id');
    }
} 