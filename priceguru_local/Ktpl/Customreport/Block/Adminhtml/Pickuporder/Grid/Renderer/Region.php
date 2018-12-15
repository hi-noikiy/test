<?php

class Ktpl_Customreport_Block_Adminhtml_Pickuporder_Grid_Renderer_Region extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        //$html = parent::render($row);
        //$url = Mage::helper("adminhtml")->getUrl("*/*/updateCpp");
        $rowval = $row->getData($this->getColumn()->getIndex());

 		$html = '<select id="region'.$row->getPickupId().'" name="'.$this->getColumn()->getId() . '" style="width:50px;">';
        
        if($rowval == 1) {
        	$selval1 = 'selected="selected"';
    	} elseif($rowval == 2) {
    		$selval2 = 'selected="selected"';
        } elseif($rowval == 3) {
            $selval3 = 'selected="selected"';
        } elseif($rowval == 4) {
            $selval4 = 'selected="selected"';
        } elseif($rowval == 5) {
            $selval5 = 'selected="selected"';  
    	} elseif($rowval == 6) {
            $selval6 = 'selected="selected"';  
    	} elseif($rowval == 7) {
            $selval7 = 'selected="selected"';  
    	} elseif($rowval == 8) {
            $selval8 = 'selected="selected"';  
    	}
        else {
    		$selval1 = ""; $selval2 = ""; $selval3 = ""; $selval4 = ""; $selval5 = ""; $selval6 = "";$selval7 = "";$selval8 = "";
    	}
    	$html .= '<option value=""></option>';
    	$html .= '<option value="1" '.$selval1.'>1</option>';
        $html .= '<option value="2" '.$selval2.'>2</option>';
        $html .= '<option value="3" '.$selval3.'>3A</option>';
        $html .= '<option value="7" '.$selval7.'>3B</option>';
        $html .= '<option value="4" '.$selval4.'>4</option>';
        $html .= '<option value="5" '.$selval5.'>5</option>';
        $html .= '<option value="6" '.$selval6.'>6A</option>';
        $html .= '<option value="8" '.$selval8.'>6B</option>';
        $html .= '</select>';

        //$html .= '<button onclick="updateSelectField(this, '. $row->getCimorderId() .', \''. $url .'\'); return false">' . Mage::helper('customreport')->__('Update') . '</button>';
 
        return $html;
    }
    public function renderExport(Varien_Object $row) {
        $rowval =  $row->getData($this->getColumn()->getIndex());
        $val = '';
        if($rowval == 1) {
        	$val = '1';
    	} elseif($rowval == 2) {
    		$val = '2';
        } elseif($rowval == 3) {
            $val = '3A';
        } elseif($rowval == 4) {
            $val = '4';
        } elseif($rowval == 5) {
            $val = '5';
    	} elseif($rowval == 6) {
            $val = '6A';
    	} elseif($rowval == 7) {
            $val = '3B';
    	} elseif($rowval == 8) {
            $val = '6B';
    	}
        return $val;
    }
}