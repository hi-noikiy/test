<?php

class Mage_Adminhtml_Block_Sales_Invoice_Renderer_Paymentmethod extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
/*
 *  Get User email by order id
 */
    public function render(Varien_Object $row) {          	      

        if($row->getIscimorder()) {
	    	$payment = "CIM";
	    } elseif($row->getMethod() == "cashondelivery") {
	    	$payment = "Pay on Delivery";
	    } elseif($row->getMethod() == "banktransfer") {
	    	$payment = "Internet Banking";
	    } elseif($row->getMethod() == "migs_hosted") {
	    	$payment = "Credit Card";
	    } else {
	    	$payment = $row->getMethod();
	    }
			
		return $payment;
	}
}