<?php

/**
 * Tracking Renderer
 * User: zbych
 */
class FFDX_ShippingBox_Block_Adminhtml_Sales_Order_Grid_Column_Renderer_Status extends FFDX_ShippingBox_Block_Adminhtml_Sales_Order_Grid_Column_Renderer
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
            $value = $tracking->getChecked() ? 'Yes' : 'No';
        }

        return $value;
    }
}