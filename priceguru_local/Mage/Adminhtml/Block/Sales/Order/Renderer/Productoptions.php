<?php

class Mage_Adminhtml_Block_Sales_Order_Renderer_Productoptions extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
/*
 *  Get User email by order id
 */
    public function render(Varien_Object $row) {          	      
	    $order_id = $row->getId();
	    $order = Mage::getModel('sales/order')->load($order_id);
	 
	    foreach($order->getAllVisibleItems() as $item) {
      		$product[] = $item->getProductOptions();
      		//echo "<pre>"; var_dump($product); die();
   		}

   		return ($product[0]['options'][0]['value'] != "") ? $product[0]['options'][0]['value'] : "-";
	}
}