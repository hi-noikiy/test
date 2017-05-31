<?php
namespace Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer;
class Region extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(\Magento\Framework\DataObject $row)
    {
               
        $rowval = $row->getData($this->getColumn()->getIndex()); 

 		$html = '<select id="region'.$row->getPickupId().'" name="'.$this->getColumn()->getId() . '" style="width:50px;">';
        $selval1 = ""; $selval2 = ""; $selval3 = ""; $selval4 = ""; $selval5 = ""; $selval6 = "";$selval7 = "";$selval8 = "";
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
 
        return $html;
    }
}