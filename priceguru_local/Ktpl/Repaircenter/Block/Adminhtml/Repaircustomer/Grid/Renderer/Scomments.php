<?php

class Ktpl_Repaircenter_Block_Adminhtml_Repaircustomer_Grid_Renderer_Scomments extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {

    public function render(Varien_Object $row)
    {
        $html = '<textarea rows="3" id="supplier_comments'.$row->getRepairCustomerId().'" class="input-text" name="'.$this->getColumn()->getId().'" type="text">'.$row->getData($this->getColumn()->getIndex()).'</textarea>';
        return $html;
    }
}