<?php

class FFDX_ShippingBox_Model_Tracking extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        $this->_init('ffdxshippingbox/tracking');
    }

    /**
     * Truncate table hordermanager_period from admin panel Orders Manager
     *
     * @return mixed
     */
    public function cleanAll()
    {
        $resource = Mage::getSingleton('core/resource')->getConnection('core_read');
        $trackingTable = $resource->getTableName('ffdxshippingbox_tracking');
        $historyTable = $resource->getTableName('ffdxshippingbox_tracking_history');

        $resource->query("truncate table $trackingTable");
        $resource->query("truncate table $historyTable");

        return;
    }
}