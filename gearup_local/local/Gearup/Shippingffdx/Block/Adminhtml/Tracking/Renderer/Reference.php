<?php
class Gearup_Shippingffdx_Block_Adminhtml_Tracking_Renderer_Reference
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $trackNumber = $row->getTrackingNumber();
        $ref = Mage::helper('ffdxshippingbox')->getTrackingRef($trackNumber);
        return $ref ? $ref->getRefTrackingNumber()?$ref->getRefTrackingNumber():$row->getIncrementId() : $row->getIncrementId();
    }

}
