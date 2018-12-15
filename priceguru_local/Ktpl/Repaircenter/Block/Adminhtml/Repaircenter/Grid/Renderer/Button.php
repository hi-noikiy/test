<?php

class Ktpl_Repaircenter_Block_Adminhtml_Repaircenter_Grid_Renderer_Button extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {

    public function render(Varien_Object $row)
    {
        $url = Mage::helper("adminhtml")->getUrl("*/*/updateRowFields");
        $html .= '<button onclick="updateRepaircenter(this, '. $row->getRepairId() .', \''. $url .'\'); return false">' . Mage::helper('repaircenter')->__('Update') . '</button>';
        return $html;
    }
}