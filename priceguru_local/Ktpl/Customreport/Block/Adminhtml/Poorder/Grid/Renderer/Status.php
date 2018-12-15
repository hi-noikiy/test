<?php

class Ktpl_Customreport_Block_Adminhtml_Poorder_Grid_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        $rowval = $row->getData($this->getColumn()->getIndex());
	$html = '<select id="status'.$row->getPoId().'" name="'.$this->getColumn()->getId() . '" style="width:70px;">';
        
        $selval1 = ""; $selval2 = ""; $selval3 = ""; $selval4 = ""; $selval5 = "";
        if($rowval == 1) {
        	$selval1 = 'selected="selected"';
    	} elseif($rowval == 2) {
    		$selval2 = 'selected="selected"';
        } elseif($rowval == 3) {
            $selval3 = 'selected="selected"';
        } elseif($rowval == 4) {
            $selval4 = 'selected="selected"';
    	} else {
    		$selval1 = ""; $selval2 = ""; $selval3 = ""; $selval4 = ""; $selval5 = "";
    	}
    	$html .= '<option value=""></option>';
    	$html .= '<option value="1" '.$selval1.'>Pending</option>';
        $html .= '<option value="2" '.$selval2.'>Cancel</option>';
        $html .= '<option value="3" '.$selval3.'>Complete</option>';
        $html .= '<option value="4" '.$selval4.'>Waiting for stock</option>';
        $html .= '</select>';
 
        return $html;
    }
    public function renderExport(Varien_Object $row) {
        $rowval =  $row->getData($this->getColumn()->getIndex());
        $val = '';
        if($rowval == 1) {
        	$val = 'Pending';
    	} elseif($rowval == 2) {
    		$val = 'Cancel';
        } elseif($rowval == 3) {
            $val = 'Complete';
        } elseif($rowval == 4) {
            $val = 'Waiting for stock';
        } 
        return $val;
    }
}