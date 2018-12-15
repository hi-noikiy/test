<?php

class EM_Onestepcheckout_Block_Adminhtml_Cimcreditorder_Grid_Renderer_Pgcomment extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row) {          	      
	    $order_id = $row->getData('entity_id');
	    $order = Mage::getModel('sales/order')->load($order_id);

	    $comment_content = "-";
	    $history = $order->getStatusHistoryCollection()->getFirstItem();
        if($history->getComment() != "")
        {
        	$comment_content = $history->getComment();
        }
	    return $comment_content;
    }
}