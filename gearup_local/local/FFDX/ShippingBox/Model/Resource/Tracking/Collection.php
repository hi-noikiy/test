<?php

class FFDX_ShippingBox_Model_Resource_Tracking_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('ffdxshippingbox/tracking');
    }

    public function getUncheckedTracks()
    {
        $this->getSelect()
            ->joinLeft(array('salesorder' => $this->getTable('sales/order')),
                'main_table.order_id=salesorder.entity_id',
                array(
                    'increment_id' =>'increment_id'
                )
            )->where('main_table.checked = 0');

        return $this;
    }

    public function loadByTrackLocationDate($referenceNumber, $location, $createdAt)
    {
        $referenceNumber = (string)$referenceNumber;

            $this->addFieldToFilter('tracking_number', $referenceNumber)
            ->addFieldToFilter('location', $location)
            ->addFieldToFilter('created_at', $createdAt);

        return $this;
    }

    public function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->joinLeft(array('salord' => $this->getTable('sales/order')),
                'main_table.order_id=salord.entity_id',
                array(
                    'increment_id' =>'increment_id'
                )
            );
        return $this;
    }
}