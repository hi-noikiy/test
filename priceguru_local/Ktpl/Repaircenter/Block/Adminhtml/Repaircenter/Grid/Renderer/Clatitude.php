<?php

class Ktpl_Repaircenter_Block_Adminhtml_Repaircenter_Grid_Renderer_Clatitude extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {

    public function render(Varien_Object $row)
    {
        $order_id = $row->getData('increment_id');
        $ords = $row->getData('c_latitude');

        if($ords && trim($ords) !='') { 
            $name = $ords;
        } else {
            $order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
            $name = $order->getShippingAddress()->getLatitude();
        }
        $html = '<textarea rows="3" id="c_latitude'.$row->getRepairId().'" class="input-text" name="'.$this->getColumn()->getId().'" type="text">'.$name.'</textarea>';
        return $name;
    }
    
    public function renderExport(Varien_Object $row) {
        return $row->getData($this->getColumn()->getIndex());
    }
}