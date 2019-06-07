<?php

/**
 * Abstract renderer
 */
class FFDX_ShippingBox_Block_Adminhtml_Sales_Order_Grid_Column_Renderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Trackings
     * @var array
     */
    public static $trackings;

    /**
     * Check if trackings are loaded
     * @return bool
     */
    protected function trackingsLoaded()
    {
        return isset(self::$trackings);
    }

    /**
     * Load Trackings
     */
    protected function loadTrackings()
    {
        $grid = $this->getColumn()->getGrid();
        $orders = $grid->getCollection();
        $ids = $orders->getColumnValues('entity_id');

        self::$trackings = Mage::getModel('ffdxshippingbox/tracking')
            ->getCollection()
            ->addFieldToFilter('order_id', array('in' => $ids))
            ->load();
    }

    /**
     * Tracking
     */
    protected function getTracking($orderId)
    {
        if (!$this->trackingsLoaded()) {
            $this->loadTrackings();
        }

        $item = false;
        $items = self::$trackings->getItemsByColumnValue('order_id', $orderId);

        if (count($items)) {
            $item = $items[count($items)-1];
        }

        return $item;
    }
}