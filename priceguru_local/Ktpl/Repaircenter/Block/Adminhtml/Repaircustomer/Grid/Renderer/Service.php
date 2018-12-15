<?php

class Ktpl_Repaircenter_Block_Adminhtml_Repaircustomer_Grid_Renderer_Service extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        
       $html = '<textarea id="service_order_no'.$row->getRepairCustomerId().'" class="input-text" name="'.$this->getColumn()->getId().'" type="text">'.$row->getData($this->getColumn()->getIndex()).'</textarea>';
        return $html;
    }
}