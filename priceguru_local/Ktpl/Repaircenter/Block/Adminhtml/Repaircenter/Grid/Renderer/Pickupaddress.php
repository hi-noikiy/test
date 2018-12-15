<?php

class Ktpl_Repaircenter_Block_Adminhtml_Repaircenter_Grid_Renderer_Pickupaddress extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {

    public function render(Varien_Object $row)
    {
        $html = '<textarea rows="3" id="pickupaddress'.$row->getRepairId().'" class="input-text" name="'.$this->getColumn()->getId().'" type="text">'.$row->getData($this->getColumn()->getIndex()).'</textarea>';
        return $html;
    }
}