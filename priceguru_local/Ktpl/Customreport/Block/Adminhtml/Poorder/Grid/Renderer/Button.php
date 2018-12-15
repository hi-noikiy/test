<?php

class Ktpl_Customreport_Block_Adminhtml_Poorder_Grid_Renderer_Button extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        $url = Mage::helper("adminhtml")->getUrl("*/*/updateRowFields");
        $html .= '<button onclick="updatePoOrders(this, '. $row->getPoId() .', \''. $url .'\'); return false">' . Mage::helper('customreport')->__('Update') . '</button>';
 
        return $html;
    }
}