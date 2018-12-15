<?php

class Ktpl_Customreport_Block_Adminhtml_Deliveryorder_Grid_Renderer_Latitude extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {

    public function render(Varien_Object $row)
    {
        $order_id = $row->getData('real_order_id');
        $lat = $row->getData('latitude');
        $name = '';
        if($lat && trim($lat) !='') { 
            $name = $lat;   
        } else {
            $order = Mage::getModel('sales/order')->load($order_id);
            if(!$order->getIsVirtual()){
                $name = $order->getShippingAddress()->getLatitude();
            }    
        }
        $html = '<input type="text" id="latitude' . $row->getDeliveryId() . '" name="' . $this->getColumn()->getId() . '" value="' . $name . '" class="input-text " style="width:100px">';
        return $name;
    }
    
    public function renderExport(Varien_Object $row) {
        return $row->getData($this->getColumn()->getIndex());
    }
}