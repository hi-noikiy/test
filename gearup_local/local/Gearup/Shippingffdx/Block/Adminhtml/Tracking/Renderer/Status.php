<?php
class Gearup_Shippingffdx_Block_Adminhtml_Tracking_Renderer_Status
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $value = $row->getChecked();
        $html = $value;
        if (!$value) {
            $html .= '   <a onclick="if(confirm(\'Are you sure you want to change status?\')) setLocation(\'' . $this->getUrl('adminhtml/shippingffdx/changestatus', array('tracking_id'=>$row->getTrackingId())) .'\')">';
            $html .= Mage::helper('gearup_sds')->__('Change status') . '</a>';
        }
        return $html;
    }

}
