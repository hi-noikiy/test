<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Blog
 * @version    tip
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */
class Gearup_Blog_Block_Post extends AW_Blog_Block_Post {

    public function getRating() {

        if (!$this->hasData('postRating' . $this->getPost()->getId())) {
            $resource = Mage::getSingleton('core/resource');
            $readConnection = $resource->getConnection('core_read');
            $query = 'SELECT percent FROM rating_option_vote_aggregated as blog_rating WHERE rating_id = ' .
                    new Zend_Db_Expr('(SELECT rating_id FROM  rating WHERE entity_id = ' .
                    new Zend_Db_Expr('(SELECT entity_id FROM rating_entity WHERE  entity_code like "' .
                    Gearup_Blog_Model_RateAggregate::BLOG_RATING_ENTITY . '") ) AND blog_rating.entity_pk_value=' . $this->getPost()->getId())
            );
            $this->setData('postRating' . $this->getPost()->getId(),$readConnection->fetchOne($query));
           
        }
         return $this->getData('postRating' . $this->getPost()->getId());
    }
    
    /* collection for blog  */
    public function getBlogRelatedCollection()
    {
        $categoryId = $this->getPost()->getCategory();
        $_productCollection = Mage::getModel("catalog/product")->getCollection()
                        ->addAttributeToSelect("*")
                        ->addUrlRewrite()    
                        ->addFieldToFilter('discontinued_product', array('neq' => 1));
        if($categoryId && $categoryId > 0){ 
            $_productCollection->addCategoryFilter(Mage::getModel('catalog/category')->load($categoryId));
        }    
            Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($_productCollection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($_productCollection);
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($_productCollection);
            $_productCollection->getSelect()->order(new Zend_Db_Expr('RAND()'));
            $_productCollection->setPageSize(8);
        
        return $_productCollection;         
    }
    
    /* collection for blog bestseller */
    public function getBestsellerProducts()
    {
        $storeId = (int) Mage::app()->getStore()->getId();
        $categoryId = $this->getPost()->getCategory();
        
        $date = new Zend_Date();
        $toDate = $date->setDay(1)->getDate()->get('Y-MM-dd');
        $fromDate = $date->subMonth(1)->getDate()->get('Y-MM-dd');
 
        $_productCollection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addStoreFilter()
            ->addUrlRewrite();
 
        $_productCollection->getSelect()
            ->joinLeft(
                array('aggregation' => $_productCollection->getResource()->getTable('sales/bestsellers_aggregated_monthly')),
                "e.entity_id = aggregation.product_id AND aggregation.store_id={$storeId} AND aggregation.period BETWEEN '{$fromDate}' AND '{$toDate}'",
                array('SUM(aggregation.qty_ordered) AS sold_quantity')
            )
            ->group('e.entity_id')
            ->order(array('sold_quantity DESC', 'e.created_at'));
        if($categoryId && $categoryId > 0){
            $_productCollection->addCategoryFilter(Mage::getModel('catalog/category')->load($categoryId));
        }        
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($_productCollection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($_productCollection);
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($_productCollection);
        
        $_productCollection->setPageSize(8);
        
        return $_productCollection;
    }

}
