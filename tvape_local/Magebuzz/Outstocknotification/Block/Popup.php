<?php
/*
* Copyright (c) 2015 www.magebuzz.com
*/
class Magebuzz_Outstocknotification_Block_Popup extends Mage_Catalog_Block_Product_View_Abstract {

  public function getProductCurrent($productId){
    $product = Mage::getModel('catalog/product')->load($productId);     
    return $product;
  }

  public function getAssociatedProducts($productId) {        
    $associated = array();
    $outStockItem = array();
    $currentProduct = Mage::getModel('catalog/product')->load($productId);
    $productType = $currentProduct->getTypeId();    
    if ($productType == 'grouped'){
      $associated = $currentProduct->getTypeInstance(true)
      ->getAssociatedProducts($currentProduct);
    }
    else if($productType == 'configurable'){
        $associated = Mage::getModel('catalog/product_type_configurable')->getUsedProducts('null', $currentProduct);        
      }  

      if (!empty($associated)) {
      foreach ($associated as $child) {
        if(!$child->getIsInStock()){
          $outStockItem[] = $child;
        }        
      }
    }                    
    return $outStockItem;    
  }

  public function isItemOutStock($productId){
    $associated = $this->getAssociatedProducts($productId) ;
    if(!empty($associated)){
      return true;
    }
    return false;
  }
	
	public function getSubmitUrl() {
		$url = Mage::getUrl('outstocknotification/index/stoctnotify'); 
		$url = Mage::getModel('core/url')->sessionUrlVar($url);
		return $url;
	}
}