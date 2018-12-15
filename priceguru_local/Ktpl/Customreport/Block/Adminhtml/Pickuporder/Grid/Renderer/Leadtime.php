<?php

class Ktpl_Customreport_Block_Adminhtml_Pickuporder_Grid_Renderer_Leadtime extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        $val = $row->getData($this->getColumn()->getIndex());
        
 		$html = '<span id="leadtime'.$row->getPickupId().'" class="markup-text">'.$val.'</span>';
 
        return $html;
    }
    
    public function renderExport(Varien_Object $row) {
        return $row->getData($this->getColumn()->getIndex());
    }
}