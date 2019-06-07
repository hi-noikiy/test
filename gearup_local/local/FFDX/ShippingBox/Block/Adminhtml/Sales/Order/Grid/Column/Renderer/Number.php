<?php

/**
 * Tracking Renderer
 * User: zbych
 */
class FFDX_ShippingBox_Block_Adminhtml_Sales_Order_Grid_Column_Renderer_Number extends FFDX_ShippingBox_Block_Adminhtml_Sales_Order_Grid_Column_Renderer
{
    /**
     * Value
     * @param Varien_Object $row
     * @return string
     */
    protected function _getValue(Varien_Object $row)
    {
        $tracking = $this->getTracking($row['entity_id']);
        $value = '';
        if ($tracking !== false) {
            $url = Mage::helper("adminhtml")->getUrl("ffdxshippingbox/adminhtml_tracking/history", array('tracking_id' => $tracking->getId()));
            $value = sprintf('<a target="_blank" href="%s" style="color:#2f2f2f; text-decoration: none;">%s</a>', $url, $tracking->getTrackingNumber());
        }

        return $value;
    }
}