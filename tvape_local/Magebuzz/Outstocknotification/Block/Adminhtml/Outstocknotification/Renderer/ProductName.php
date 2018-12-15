<?php
/*
 * @copyright (c) 2015 www.magebuzz.com
 */ 
class Magebuzz_Outstocknotification_Block_Adminhtml_Outstocknotification_Renderer_ProductName extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text {	
	public function render(Varien_Object $row) {
		$productId = $row->getProductId(); 
		$sock = Mage::getModel('catalog/product')->load($productId);
		return $sock->getName();
	}
}