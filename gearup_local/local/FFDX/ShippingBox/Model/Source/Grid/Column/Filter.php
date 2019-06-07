<?php

/**
 * Filter Status
 */
class FFDX_ShippingBox_Model_Source_Grid_Column_Filter extends Varien_Object
{
    /**
     * Filter by Number
     * @param Mage_Sales_Model_Resource_Order_Collection $collection
     * @param Mage_Adminhtml_
     */
    public function loadTrackingsByNumber($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        /** @var Varien_Data_Collection_Db $trackings */
        $trackings = Mage::getModel('ffdxshippingbox/tracking')
            ->getCollection()
            ->addFieldToFilter('tracking_number', array('like' => '%' . $value . '%'));

        $ids = $trackings->getColumnValues('order_id');

        $collection->addFieldToFilter('entity_id', array('in' => $ids));
    }

    /**
     * Filter by Status
     */
    public function loadTrackingsByStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();
        /** @var Varien_Data_Collection_Db $trackings */
        $trackings = Mage::getModel('ffdxshippingbox/tracking')
            ->getCollection()
            ->addFieldToFilter('checked', (int)$value);

        $ids = $trackings->getColumnValues('order_id');

        $collection->addFieldToFilter('entity_id', array('in' => $ids));
    }
}