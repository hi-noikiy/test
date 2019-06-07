<?php
class Gearup_Shippingffdx_Block_Adminhtml_History_Renderer_Location
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $value = $row->getlocation();
        $html = $value;
        if (!$value || strtolower($value) == strtolower('Unknown')) {
            $html = $this->getLastLocation($row->getTrackingId());
        } else {
            $html = $value;
        }
        return $html;
    }

    public function getLastLocation($track) {
        $history = Mage::getModel('ffdxshippingbox/history')->getCollection();
        $history->addFieldToFilter('tracking_id', array('eq'=>$track));
        $history->addFieldToFilter('location', array('neq'=>'Unknown'));
        $history->setOrder('history_id','DESC');
        
        $track = $history->getFirstItem();
        return $track->getLocation();
    }
}
