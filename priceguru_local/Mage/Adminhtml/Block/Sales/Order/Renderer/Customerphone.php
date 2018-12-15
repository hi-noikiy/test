<?php

class Mage_Adminhtml_Block_Sales_Order_Renderer_Customerphone extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
/*
 *  Get User email by order id
 */
    public function render(Varien_Object $row) {          	      
	    $order_id = $row->getId();
	    $order = Mage::getModel('sales/order')->load($order_id);
	    $billaddress = $order->getBillingAddress();
	    $phone = $billaddress->getData('telephone');
	    //$customerId = $order->getCustomerId();
	    //$customer = Mage::getModel('customer/customer')->load($customerId);
	    //$phone = $customer->getPrimaryBillingAddress()->getTelephone();
		if($phone == "") {
			$phone ='-';
		}

		return $phone;
	}
}