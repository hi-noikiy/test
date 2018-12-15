<?php
/*
* @Copyright (c) 2015 www.magebuzz.com
*/ 
class Magebuzz_Outstocknotification_Block_Product_View_Configurable extends Mage_Catalog_Block_Product_View {
  protected function _prepareLayout() { 
    if(Mage::helper('outstocknotification')->enableModule()) {   
      $configurableBlock = $this->getLayout()->getBlock('product.info.configurable');
      if ($configurableBlock) {
        $configurableBlock->setTemplate('outstocknotification/catalog/product/view/type/configurable.phtml');
      }  
    }
  }

  public function getItemOutStock($product){    
    $outStockItem = array();
    $childProducts = Mage::getModel('catalog/product_type_configurable')->getUsedProducts('null', $product);        
    if (!empty($childProducts)) {
      foreach ($childProducts as $child) {
        if(!$child->getIsInStock()){
          $outStockItem[] = $child;
        }        
      }
    }                    
    return $outStockItem;    
  }
}