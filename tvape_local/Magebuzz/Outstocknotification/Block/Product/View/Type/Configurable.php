<?php
/*
* @copyright (c) 2015 www.magebuzz.com
*/ 
class Magebuzz_Outstocknotification_Block_Product_View_Type_Configurable extends Mage_Catalog_Block_Product_View_Type_Configurable {   
	public function getAllowProducts() {
		if (!$this->hasAllowProducts()) {
			$products = array();
			$allProducts = $this->getProduct()
				->getTypeInstance(true)
				->getUsedProducts(null, $this->getProduct());
			
			foreach ($allProducts as $product) {
				if (!$product->isSaleable()) {
					$products[] = $product;
				}
			}
			$this->setAllowProducts($products);
		}
		return $this->getData('allow_products');
	}
}