<?php

/**
 * Class FFDX_ShippingBox_Model_Resource_History_Collection
 */
class FFDX_ShippingBox_Model_Resource_History_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * _construct
     */
    protected function _construct()
    {
        $this->_init('ffdxshippingbox/history');
    }

    /**
     * load history of tracking from table ffdxshippingbox_tracking_history
     *
     * @param $trackingId
     * @param $activity
     * @param $location
     * @param $eventId
     * @return $this
     */
    public function loadHistoryOld($trackingId, $activity, $location, $createdAt)
    {
        $this
            ->addFieldToFilter('tracking_id', $trackingId)
            ->addFieldToFilter('activity', $activity)
            ->addFieldToFilter('location', $location)
            ->addFieldToFilter('created_at', $createdAt);

        return $this;
    }
    
  /**
     * load history of tracking from table ffdxshippingbox_tracking_history
     *
     * @param $trackingId
     * @param $event
     * @param $location
     * @return $this
     */
    public function loadHistory($trackingId, $event, $location, $createdAt)
    {
        $this
            ->addFieldToFilter('tracking_id', $trackingId)
            ->addFieldToFilter('event', $event);
//            ->addFieldToFilter('location', $location)
//            ->addFieldToFilter('created_at', $createdAt);

        return $this;
    }    

    public function getCompleteTrackingData($orderId)
    {
        $this->getSelect()->join(
            array(
                'ffdxtrack' => $this->getTable('ffdxshippingbox/tracking')),
                'main_table.tracking_id=ffdxtrack.tracking_id',
            array(
                'tracking_id'   => 'tracking_id',
                'order_id'      => 'order_id',
            )
        )->where('order_id = ?', $orderId);

        return $this;
    }

}