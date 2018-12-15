<?php

class Mage_Adminhtml_Block_Sales_Order_Renderer_Totalqty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
/*
 *  Get User email by order id
 */
    public function render(Varien_Object $row) {          	      
	    
    	$qty = $row->getData('total_qty_ordered');
   		return round($qty);
	}
}