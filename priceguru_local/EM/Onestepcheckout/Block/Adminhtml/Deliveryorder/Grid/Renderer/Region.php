<?php

class EM_Onestepcheckout_Block_Adminhtml_Deliveryorder_Grid_Renderer_Region extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        //$html = parent::render($row);
        //$url = Mage::helper("adminhtml")->getUrl("*/*/updateCpp");
        $rowval = $row->getData($this->getColumn()->getIndex());

 		$html = '<select id="region'.$row->getDeliveryId().'" name="'.$this->getColumn()->getId() . '" style="width:50px;">';
        
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
    	} else {
    		$selval1 = ""; $selval2 = ""; $selval3 = ""; $selval4 = ""; $selval5 = "";
    	}
    	$html .= '<option value=""></option>';
    	$html .= '<option value="1" '.$selval1.'>1</option>';
        $html .= '<option value="2" '.$selval2.'>2</option>';
        $html .= '<option value="3" '.$selval3.'>3</option>';
        $html .= '<option value="4" '.$selval4.'>4</option>';
        $html .= '<option value="5" '.$selval5.'>5</option>';
        $html .= '</select>';

        //$html .= '<button onclick="updateSelectField(this, '. $row->getCimorderId() .', \''. $url .'\'); return false">' . Mage::helper('onestepcheckout')->__('Update') . '</button>';
 
        return $html;
    }
}