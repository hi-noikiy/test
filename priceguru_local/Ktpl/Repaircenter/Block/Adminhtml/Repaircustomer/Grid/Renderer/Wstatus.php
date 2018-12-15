<?php

class Ktpl_Repaircenter_Block_Adminhtml_Repaircustomer_Grid_Renderer_Wstatus extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {

    public function render(Varien_Object $row)
    {
        $rowval = $row->getData($this->getColumn()->getIndex());
	$html = '<select id="warranty_status'.$row->getRepairCustomerId().'" name="'.$this->getColumn()->getId() . '">';
        
        if($rowval == 1) {
        	$selvalyes = 'selected="selected"';
    	} elseif($rowval == 2) {
    		$selvalno = 'selected="selected"';
    	} else {
    		$selvalyes = "";
    		$selvalno = "";
    	}
    	$html .= '<option value=""></option>';
    	$html .= '<option value="1" '.$selvalyes.'>Warranty Ok</option>';
        $html .= '<option value="2" '.$selvalno.'>Warranty Void</option>';
        $html .= '</select>';

        return $html;
    }
}