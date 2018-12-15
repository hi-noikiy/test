<?php

class Ktpl_Repaircenter_Block_Adminhtml_Repaircustomer_Grid_Renderer_Leadtime extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {

    public function render(Varien_Object $row)
    {
        $val = $row->getData($this->getColumn()->getIndex());
	$html = '<span id="leadtime'.$row->getRepairCustomerId().'" class="markup-text">'.$val.'</span>';
        return $html;
    }
}