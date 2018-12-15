<?php

class Ktpl_Customreport_Block_Adminhtml_Poorder_Grid_Renderer_Markup extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        $val = $row->getData($this->getColumn()->getIndex());
        if($val != "" && $val > 0) {
        	$val = $val."%";
        }
 		$html = '<span id="markup'.$row->getPoId().'" class="markup-text">'.$val.'</span>';
 
        return $html;
    }
    
    public function renderExport(Varien_Object $row) {
        return $row->getData($this->getColumn()->getIndex());
    }
}