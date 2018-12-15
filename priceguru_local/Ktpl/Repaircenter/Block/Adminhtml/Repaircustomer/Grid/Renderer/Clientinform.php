<?php

class Ktpl_Repaircenter_Block_Adminhtml_Repaircustomer_Grid_Renderer_Clientinform extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {

    public function render(Varien_Object $row)
    {
        $rowval = $row->getData($this->getColumn()->getIndex());
	$html = '<select id="client_informed'.$row->getRepairCustomerId().'" name="'.$this->getColumn()->getId() . '">';
        
        if($rowval == 1) {
        	$selvalyes = 'selected="selected"';
    	} elseif($rowval == 0) {
    		$selvalno = 'selected="selected"';
    	} else {
    		$selvalyes = "";
    		$selvalno = "";
    	}
    	$html .= '<option value=""></option>';
    	$html .= '<option value="1" '.$selvalyes.'>Yes</option>';
        $html .= '<option value="0" '.$selvalno.'>No</option>';
        $html .= '</select>';

        return $html;
    }
}