<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Mostviewed
 */
class Amasty_Mostviewed_Block_Catalog_Product_List_Upsell extends Mage_Catalog_Block_Product_List_Upsell
{
    protected function _prepareData()
    {
        if (!Mage::getStoreConfig('ammostviewed/up_sells/enabled')
            || (Mage::getStoreConfig('ammostviewed/up_sells/show_for_out_of_stock_only')
                && Mage::registry('product')->getIsInStock())
        ) {
            return parent::_prepareData();
        }
        $product = Mage::registry('product');

        if (!$product) {
            return parent::_prepareData();
        }
        $currentProductId = Mage::registry('product')->getId();
        $manuallyAdded = (int) Mage::getStoreConfig('ammostviewed/up_sells/manually');

        switch ($manuallyAdded) {
            case Amasty_Mostviewed_Model_Source_Manually::APPEND:
                parent::_prepareData();
                if (Mage::getStoreConfig('ammostviewed/up_sells/size') > $this->_itemCollection->getSize()) {
                    $collection = Mage::helper('ammostviewed')->getViewedWith($currentProductId, 'up_sells');
                    if (0 < $collection->getSize()) {
                        $this->_itemCollection = $collection;
                    }
                }
                break;
            case Amasty_Mostviewed_Model_Source_Manually::REPLACE:
                $this->_itemCollection = Mage::helper('ammostviewed')->getViewedWith($currentProductId, 'up_sells');
                if (!$this->_itemCollection->getSize()) {
                    parent::_prepareData();
                }
                break;
            case Amasty_Mostviewed_Model_Source_Manually::NOTHING:
                parent::_prepareData();
                if (!$this->_itemCollection->getSize()) {
                    $this->_itemCollection = Mage::helper('ammostviewed')->getViewedWith($currentProductId, 'up_sells');
                }
                break;
        }
        if(count($this->_itemCollection->getSize()) > 4){
        	return $this;
        } else {

        	$categoryId = $product->getCategoryIds();
            $finalPrice = $product->getFinalPrice();
                
        	$productCollection = Mage::getModel('catalog/product')->getCollection()
        		->addAttributeToSelect('*')
        		->addFieldToFilter('entity_id', array('neq' => $product->getId()))
                ->addFieldToFilter('price', array('gt' => $finalPrice))
			    ->addCategoryFilter(Mage::getModel('catalog/category')->load($categoryId[0]));
			    Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($productCollection);
				Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($productCollection);
				Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($productCollection); 
				$productCollection->getSelect()->order('RAND()');
				$productCollection->getSelect()->limit(8);

			$this->_itemCollection = $productCollection;
        return $this;
    }

}
}
