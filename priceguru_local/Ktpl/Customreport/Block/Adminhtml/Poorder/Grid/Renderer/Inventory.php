<?php

class Ktpl_Customreport_Block_Adminhtml_Poorder_Grid_Renderer_Inventory extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        $rowval = $row->getData($this->getColumn()->getIndex());
	$html = '<select id="inventory'.$row->getPoId().'" name="'.$this->getColumn()->getId() . '" style="width:70px;">';
        
        $selval1 = ""; $selval2 = ""; $selval3 = ""; 
        if($rowval == 1) {
        	$selval1 = 'selected="selected"';
    	} elseif($rowval == 2) {
    		$selval2 = 'selected="selected"';
        } elseif($rowval == 3) {
            $selval3 = 'selected="selected"';
    	} else {
    		$selval1 = ""; $selval2 = ""; $selval3 = ""; 
    	}
    	
    	$html .= '<option value="1" '.$selval1.'></option>';
        $html .= '<option value="2" '.$selval2.'>Yes</option>';
        $html .= '<option value="3" '.$selval3.'>Credit Note</option>';
        $html .= '</select>';
 
        return $html;
    }
    public function renderExport(Varien_Object $row) {
        $rowval =  $row->getData($this->getColumn()->getIndex());
        $val = '';
        if($rowval == 1) {
        	$val = '';
    	} elseif($rowval == 2) {
    		$val = 'Yes';
        } elseif($rowval == 3) {
            $val = 'Credit Note';
        } 
        return $val;
    }
}