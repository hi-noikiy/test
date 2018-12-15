<?php
/*
 * @copyright (c) 2015 www.magebuzz.com
 */ 
class Magebuzz_Outstocknotification_Block_Adminhtml_Outstocknotification_Renderer_Count extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text {	
	public function render(Varien_Object $row) {
		$productId = $row->getProductId(); 
		$sock = Mage::getModel('productalert/stock')->getCollection();		
		$sock->addFieldToFilter('product_id',Array('pid'=>$productId));
		return count($sock->getData());			
	}
}