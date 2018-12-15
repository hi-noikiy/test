<?php

class Ktpl_Repaircenter_Block_Adminhtml_Repaircenter_Grid_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {

    public function render(Varien_Object $row)
    {
        $rowval = $row->getData($this->getColumn()->getIndex());

 		$html = '<select id="status'.$row->getRepairId().'" name="'.$this->getColumn()->getId() . '" style="width:70px;">';
        
        if($rowval == 1) {
        	$selval1 = 'selected="selected"';
    	} elseif($rowval == 2) {
    		$selval2 = 'selected="selected"';
        } elseif($rowval == 3) {
            $selval3 = 'selected="selected"';
       	} else {
    		$selval1 = ""; $selval2 = ""; $selval3 = "";
    	}
    	$html .= '<option value=""></option>';
    	$html .= '<option value="1" '.$selval1.'>Pending</option>';
        $html .= '<option value="2" '.$selval2.'>Complete</option>';
        $html .= '<option value="3" '.$selval3.'>SAV Home</option>';
        $html .= '</select>';
 
        return $html;
    }
}