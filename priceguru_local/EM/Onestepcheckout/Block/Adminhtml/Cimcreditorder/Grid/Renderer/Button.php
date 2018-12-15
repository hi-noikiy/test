<?php

class EM_Onestepcheckout_Block_Adminhtml_Cimcreditorder_Grid_Renderer_Button extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        //$html = parent::render($row);
        $url = Mage::helper("adminhtml")->getUrl("*/*/updateRowFields");
        //$rowval = $row->getData($this->getColumn()->getIndex());

        $html .= '<button onclick="updateRowFields(this, '. $row->getCimorderId() .', \''. $url .'\'); return false">' . Mage::helper('onestepcheckout')->__('Update') . '</button>';
 
        return $html;
    }
}