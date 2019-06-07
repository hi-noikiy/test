<?php
class Gearup_Shippingffdx_Block_Adminhtml_Tracking_Renderer_External
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $html = '<a target="_blank" href="http://www.postaplus.com/Customer/ShipmentDetails.aspx?sno='.$row->getTrackingNumber().'&RefNo='.$row->getIncrementId().'">';
        $html .= Mage::helper('gearup_sds')->__('Open') . '</a>';
        return $html;
    }

}
