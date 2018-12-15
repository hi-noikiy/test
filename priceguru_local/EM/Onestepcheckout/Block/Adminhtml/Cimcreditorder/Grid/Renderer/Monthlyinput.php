<?php

class EM_Onestepcheckout_Block_Adminhtml_Cimcreditorder_Grid_Renderer_Monthlyinput extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        $html = parent::render($row);
        //$url = Mage::helper("adminhtml")->getUrl("*/*/updateTitle");
 		$html = '<input type="text" id="monthly'.$row->getCimorderId().'" name="'.$this->getColumn()->getId().'" value="'.$row->getData($this->getColumn()->getIndex()).'" class="input-text ">';
        //$html .= '<button onclick="updateField(this, '. $row->getCimorderId() .', \''. $url .'\'); return false">' . Mage::helper('onestepcheckout')->__('Update') . '</button>';
 
        return $html;
    }
}