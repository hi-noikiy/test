<?php

class Ktpl_Customreport_Block_Adminhtml_Cimcreditorder_Grid_Renderer_Deposit extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        $html = parent::render($row);
        //$url = Mage::helper("adminhtml")->getUrl("*/*/updateDeposit");
 		$html = '<input type="text" id="deposit'.$row->getCimorderId().'" name="'.$this->getColumn()->getId().'" value="'.$row->getData($this->getColumn()->getIndex()).'" class="input-text ">';
 
        return $html;
    }
}