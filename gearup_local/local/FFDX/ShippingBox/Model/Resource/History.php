<?php

class FFDX_ShippingBox_Model_Resource_History extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('ffdxshippingbox/history', 'history_id');
    }
} 