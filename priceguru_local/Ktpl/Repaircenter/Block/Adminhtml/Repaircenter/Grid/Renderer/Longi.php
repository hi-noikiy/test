<?php

class Ktpl_Repaircenter_Block_Adminhtml_Repaircenter_Grid_Renderer_Longi extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {

    public function render(Varien_Object $row)
    {
        $html = '<span id="sc_longitude'.$row->getRepairId().'" class="markup-text" style="width:100px">'.$row->getData($this->getColumn()->getIndex()).'</span>';
        return $html;
    }
    
    public function renderExport(Varien_Object $row) {
        return $row->getData($this->getColumn()->getIndex());
    }
}