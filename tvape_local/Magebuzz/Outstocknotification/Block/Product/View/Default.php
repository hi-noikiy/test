<?php
/*
* @Copyright (c) 2015 www.magebuzz.com
*/ 
class Magebuzz_Outstocknotification_Block_Product_View_Default extends Mage_Catalog_Block_Product_View {
	protected function _prepareLayout() {
		$simpleBlock = $this->getLayout()->getBlock('product.info.simple');
		$virtualBlock = $this->getLayout()->getBlock('product.info.virtual');
		$downloadBlock = $this->getLayout()->getBlock('product.info.downloadable');  
		$bunndle = $this->getLayout()->getBlock('product.info.bundle');  
		$outstockenable = Mage::helper('outstocknotification')->enableModule();   
		if ($outstockenable) {
			if ($simpleBlock) {
				$simpleBlock->setTemplate('outstocknotification/catalog/product/view/type/default.phtml');
			}
			else if ($virtualBlock) {
				$virtualBlock->setTemplate('outstocknotification/catalog/product/view/type/default.phtml');
			}
			else if ($downloadBlock) {
				$downloadBlock->setTemplate('outstocknotification/catalog/product/view/type/default.phtml');
			}else if($bunndle) {
				$bunndle->setTemplate('outstocknotification/catalog/product/view/type/default.phtml');    
			}
		}
	}
}