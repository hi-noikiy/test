<?php

class Ktpl_Customreport_Block_Adminhtml_Pickuporder_Grid_Renderer_Delivery extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        //$html = parent::render($row);
        //$url = Mage::helper("adminhtml")->getUrl("*/*/updateCpp");
        $rowval = $row->getData($this->getColumn()->getIndex());

 		$html = '<select id="delivery'.$row->getPickupId().'" name="'.$this->getColumn()->getId() . '">';
        
        if($rowval == 1) {
        	$selvalyes = 'selected="selected"';
    	} elseif($rowval == 2) {
    		$selvalno = 'selected="selected"';
    	} else {
    		$selvalyes = "";
    		$selvalno = "";
    	}
    	$html .= '<option value=""></option>';
    	$html .= '<option value="1" '.$selvalyes.'>Yes</option>';
        $html .= '<option value="2" '.$selvalno.'>No</option>';
        $html .= '</select>';

        //$html .= '<button onclick="updateSelectField(this, '. $row->getCimorderId() .', \''. $url .'\'); return false">' . Mage::helper('onestepcheckout')->__('Update') . '</button>';
 
        return $html;
    }

    public function renderExport(Varien_Object $row) {
        return ( $row->getData($this->getColumn()->getIndex()) == 1 )?'Yes' : 'No';
    }
    
}