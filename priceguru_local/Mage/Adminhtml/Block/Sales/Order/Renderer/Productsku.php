<?php

class Mage_Adminhtml_Block_Sales_Order_Renderer_Productsku extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
/*
 *  Get User email by order id
 */
    public function render(Varien_Object $row) {          	      
	    $order_id = $row->getId();
	    $order = Mage::getModel('sales/order')->load($order_id);
	    $items = $order->getAllVisibleItems();
	    foreach($items as $i) {
      		$product[] = $i->getSku();
   		}
		return $product[0];
	}
}