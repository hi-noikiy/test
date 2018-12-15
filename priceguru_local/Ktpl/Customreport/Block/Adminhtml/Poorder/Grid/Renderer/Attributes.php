<?php

class Ktpl_Customreport_Block_Adminhtml_Poorder_Grid_Renderer_Attributes extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        $html = parent::render($row);
 		$html = '<input type="text" id="attributes'.$row->getPoId().'" name="'.$this->getColumn()->getId().'" value="'.$row->getData($this->getColumn()->getIndex()).'" class="input-text ">';
        return $html;
    }
    public function renderExport(Varien_Object $row) {
        return $row->getData($this->getColumn()->getIndex());
    }
}