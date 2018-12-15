<?php

class Mage_Adminhtml_Block_Sales_Order_Renderer_Paymentmethod extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
/*
 *  Get User email by order id
 */
    public function render(Varien_Object $row) {          	      
	    //$order_id = $row->getId();
	    //$order = Mage::getModel('sales/order')->load($order_id);
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
	    //$payment = ($row->getIscimorder()) ? "CIM":$row->getMethod();
			
		return $payment;
	}
}