<?php

class Ktpl_Customreport_Block_Adminhtml_Poorder_Grid_Renderer_Wholesaleprice extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        //$html = parent::render($row);
        $url = Mage::helper("adminhtml")->getUrl("*/*/updateMarkupField");
 		$html = '<input type="text" id="wholesale_price'.$row->getPoId().'" name="'.$this->getColumn()->getId().'" value="'.$row->getData($this->getColumn()->getIndex()).'" class="input-text ">';
        $html .= '<button onclick="updateMarkupField(this, '. $row->getPoId() .', \''. $url .'\'); return false">' . Mage::helper('customreport')->__('Update') . '</button>';
 
        return $html;
    }
    public function renderExport(Varien_Object $row) {
        return $row->getData($this->getColumn()->getIndex());
    }
}