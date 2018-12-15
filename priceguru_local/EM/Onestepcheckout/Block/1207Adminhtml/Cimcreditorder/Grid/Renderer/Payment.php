<?php

class EM_Onestepcheckout_Block_Adminhtml_Cimcreditorder_Grid_Renderer_Payment extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        //$html = parent::render($row);
        $url = Mage::helper("adminhtml")->getUrl("*/*/updatePayment");
        $rowval = $row->getData($this->getColumn()->getIndex());

 		$html = '<select id="payment'.$row->getCimorderId().'" name="'.$this->getColumn()->getId() . '">';
        
        if($rowval == "Standing Order") {
        	$selstading = 'selected="selected"';
    	} elseif($rowval == "Caisse") {
    		$selcaisse = 'selected="selected"';
    	} else {
    		$selstading = "";
    		$selcaisse = "";
    	}
    	$html .= '<option value=""></option>';
    	$html .= '<option value="Standing Order" '.$selstading.'>Standing Order</option>';
        $html .= '<option value="Caisse" '.$selcaisse.'>Caisse</option>';
        $html .= '</select>';

        //$html .= '<button onclick="updateSelectField(this, '. $row->getCimorderId() .', \''. $url .'\'); return false">' . Mage::helper('onestepcheckout')->__('Update') . '</button>';
 
        return $html;
    }
}