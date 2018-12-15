<?php

class Ktpl_Repaircenter_Block_Adminhtml_Repaircenter_Grid_Renderer_Caddress extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {

    public function render(Varien_Object $row)
    {
        $order_id = $row->getData('increment_id');
        $ords = $row->getData('c_address');

        if($ords && trim($ords) !='') { 
            $name = $ords;
        } else {
            $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
            $name = $order->getShippingAddress()->getStreetFull();
        }
        $html = '<textarea rows="3" id="c_address'.$row->getRepairId().'" class="input-text" name="'.$this->getColumn()->getId().'" type="text">'.$name.'</textarea>';
        return $html;
    }
    
    public function renderExport(Varien_Object $row) {
        return $row->getData($this->getColumn()->getIndex());
    }
}