<?php
/**
* 
*/

class Gearup_SuggestedProducts_Block_Suggested extends Mage_Catalog_Block_Product_Abstract
{
    protected $samebrandids = [0];
    protected $bestsellids = [0];
    protected $samepriceids = [0];
    
    public function getSameBrandCollection()
    {
        $product = Mage::registry('current_product');
        $categoryId = $product->getCategoryIds();
        $manufacturer = $product->getManufacturer();
        $_productCollection = new Varien_Data_Collection();
        if(isset($categoryId) && isset($categoryId[0])){
            $_productCollection = Mage::getModel("catalog/product")->getCollection()
                        ->addAttributeToSelect("*")
                        ->addUrlRewrite()
                        ->addCategoryFilter(Mage::getModel('catalog/category')->load($categoryId[0]))
                        ->addAttributeToFilter('manufacturer', $manufacturer)
                        ->addFieldToFilter('entity_id', array('neq' => $product->getId()))
                        ->addFieldToFilter('discontinued_product', array('neq' => 1));

            Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($_productCollection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($_productCollection);
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($_productCollection);
            $_productCollection->getSelect()->order(new Zend_Db_Expr('RAND()'));
            $_productCollection->setPageSize(8);
            $this->samebrandids = $_productCollection->getAllIds();
        }    
        
        return $_productCollection;         
    }
    
    public function getBestsellerCollection()
    {
        $product = Mage::registry('current_product');
        $categoryId = $product->getCategoryIds();
        $_productCollection = new Varien_Data_Collection();
        $storeId = (int) Mage::app()->getStore()->getId();
        if(isset($categoryId) && isset($categoryId[0])){

            $_productCollection = Mage::getModel("catalog/product")->getCollection()
                        ->addAttributeToSelect("*")
                        ->addCategoryFilter(Mage::getModel('catalog/category')->load($categoryId[0]))
                        ->addFieldToFilter('entity_id', array('neq' => $product->getId()))
                        ->addFieldToFilter('entity_id', array('nin' => $this->samebrandids))
                        ->addFieldToFilter('discontinued_product', array('neq' => 1))
                        ->addUrlRewrite();
                        $_productCollection->getSelect()
                        ->joinLeft(
                            array('aggregation' => $_productCollection->getResource()->getTable('sales/bestsellers_aggregated_monthly')),
                            "e.entity_id = aggregation.product_id AND aggregation.store_id={$storeId}",
                            array('SUM(aggregation.qty_ordered) AS sold_quantity')
                        )->group('e.entity_id')
                        ->order('sold_quantity', 'desc');

            Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($_productCollection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($_productCollection);
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($_productCollection);

            $_productCollection->setPageSize(8);
            $this->bestsellids = $_productCollection->getAllIds();
        }    
        $this->bestsellids = array_merge($this->samebrandids,$this->bestsellids);
         
        return $_productCollection;       
    }

    public function getSimilarPriceCollection()
    {
    	$product = Mage::registry('current_product');
        $categoryId = $product->getCategoryIds();
        $finalPrice = $product->getFinalPrice();
        $_productCollection = new Varien_Data_Collection();
        if(isset($categoryId) && isset($categoryId[0])){
            $_productCollection = Mage::getModel("catalog/product")->getCollection()
                        ->addAttributeToSelect("*")
                        ->addUrlRewrite()
                        ->addAttributeToFilter('price', $finalPrice)
                        ->addCategoryFilter(Mage::getModel('catalog/category')->load($categoryId[0]))
                        ->addFieldToFilter('entity_id', array('neq' => $product->getId()))
                        ->addFieldToFilter('entity_id', array('nin' => $this->bestsellids))
                        ->addFieldToFilter('discontinued_product', array('neq' => 1));

            Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($_productCollection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($_productCollection);
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($_productCollection);                

            $_productCollection->setPageSize(8);
            $this->samepriceids = $_productCollection->getAllIds();
        }    
        $this->samepriceids = array_merge($this->bestsellids,$this->samepriceids);
        return $_productCollection; 
    }

    public function getBestReviewCollection()
    {
        $product = Mage::registry('current_product');
        $categoryId = $product->getCategoryIds();
        $_productCollection = new Varien_Data_Collection();
        if(isset($categoryId) && isset($categoryId[0])){
            $_productCollection = Mage::getModel("catalog/product")->getCollection()
                        ->addAttributeToSelect("*")
                        ->addCategoryFilter(Mage::getModel('catalog/category')->load($categoryId[0]))
                        //->addFieldToFilter('entity_id', array('nin' => $this->samepriceids))
                        ->addUrlRewrite()
                        ->joinField('rating_score', 
                            'review_entity_summary', 
                            'rating_summary', 
                            'entity_pk_value=entity_id', 
                            array('entity_type'=>1, 'store_id'=> Mage::app()->getStore()->getId(),'rating_summary'=> 100),
                            'right'
                        )
                        ->addFieldToFilter('discontinued_product', array('neq' => 1));

            Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($_productCollection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($_productCollection);
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($_productCollection);

            $_productCollection->setPageSize(8);
        }

        return $_productCollection;            
    }
    
    public function getSamecategoryCollection()
    {
    	$product = Mage::registry('current_product');
        $categoryId = $product->getCategoryIds();
        $_productCollection = new Varien_Data_Collection();
        if(isset($categoryId) && isset($categoryId[0])){
            $_productCollection = Mage::getModel("catalog/product")->getCollection()
                        ->addAttributeToSelect("*")
                        ->addUrlRewrite()
                        ->addCategoryFilter(Mage::getModel('catalog/category')->load($categoryId[0]))
                        ->addFieldToFilter('entity_id', array('neq' => $product->getId()))
                        ->addFieldToFilter('discontinued_product', array('neq' => 1));

            Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($_productCollection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($_productCollection);
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($_productCollection);
            $_productCollection->getSelect()->order(new Zend_Db_Expr('RAND()'));
            $_productCollection->setPageSize(8);
        }
        return $_productCollection; 
    }
}