<?php

class Ktpl_Repaircenter_Block_Adminhtml_Repaircenter_Grid_Renderer_Dispatch extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {

    var $status = [1 => 'Yes', 0 => 'No',];
    public function render(Varien_Object $row)
    {
        
        $rowval = $row->getData($this->getColumn()->getIndex());
        $html = '<select id="dispatch' . $row->getRepairId() . '" name="' . $this->getColumn()->getId() . '>';
        $html .= '<option value=""></option>';

        foreach ($this->status as $k => $v):
            $selected = ($k == $rowval) ? 'selected' : '';
            $html .= '<option value="' . $k . '" ' . $selected . ' >' . $v . '</option>';
        endforeach;
        $html .= '</select>';

        return $html;
    }
    
    public function renderExport(Varien_Object $row) {
        return $this->status[$row->getData($this->getColumn()->getIndex())];
    }
}