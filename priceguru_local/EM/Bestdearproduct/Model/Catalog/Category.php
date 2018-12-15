<?php
class EM_Bestdearproduct_Model_Catalog_Category extends Mage_Catalog_Model_Category
{
	public function getProductCollection()
    {
		if($this->getId()=="211"){
			$collection = Mage::getResourceModel('catalog/product_collection')
				->setStoreId($this->getStoreId())
				->addAttributeToFilter('new_best_seller', '1')
				->addCategoryFilter($this);
		}
		else
		{
			$collection = Mage::getResourceModel('catalog/product_collection')
				->setStoreId($this->getStoreId())
				->addCategoryFilter($this);
		}
        return $collection;
    }
}
		