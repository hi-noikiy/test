<?php
/**
 * MageWorkshop
 * Copyright (C) 2017 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DRCategoryRatings
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

class MageWorkshop_DRCategoryRatings_Model_Rating extends Mage_Core_Model_Abstract
{
    protected $_ratingIds = array();
    protected $_product;
    
    /**
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        return $this->_product;
    }
    
    /**
     * @param Mage_Catalog_Model_Product $product
     */
    public function setProduct(Mage_Catalog_Model_Product $product)
    {
        $this->_product = $product;
    }
    
    /**
     * @return array
     */
    public function getRatingIds()
    {
        return $this->_getCategoryRatingIds();
    }
    
    /**
     * @return array|null
     */
    protected function _getCategoryRatingIds()
    {
        if (empty($this->_ratingIds)) {
            
            if ($product = $this->getProduct()) {
                $categoryIds = array();
    
                /** @var Mage_Catalog_Model_Resource_Category_Collection $category */
                $categoryCollection = $product->getCategoryCollection();
                /** @var Mage_Catalog_Model_Category $category */
                foreach ($categoryCollection as $category) {
                    foreach ($category->getPathIds() as $categoryId) {
                        $categoryIds[$categoryId] = $categoryId;
                    }
                }
    
                if (!empty($categoryIds)) {
                    $storeRatingIds = array();
                    foreach ($this->getStoreIds() as $storeId) {
                        $resource = Mage::getSingleton('core/resource');
                        $connection = $resource->getConnection('core_read');
                        $select = $connection->select()
                            ->from(array('eav' => $resource->getTableName('eav/attribute')), 'v.value')
                            ->joinInner(array('v' => $resource->getTableName('catalog_category_entity_text')), 'eav.attribute_id = v.attribute_id', array())
                            ->joinInner(array('c' => $resource->getTableName('catalog/category')), 'c.entity_id = v.entity_id', array())
                            ->where('eav.attribute_code = ?', MageWorkshop_DRCategoryRatings_Helper_Data::RATINGS_AVAILABLE)
                            ->where('store_id = ?', $storeId)
                            ->where('v.value IS NOT NULL')
                            ->where('v.entity_id IN(?)', $categoryIds)
                            ->order(array('c.level ' . Varien_Data_Collection::SORT_ORDER_DESC, 'v.entity_id ' . Varien_Data_Collection::SORT_ORDER_ASC));
    
                        $storeRatingIds[$storeId] = explode(',', $connection->fetchOne($select));
                    }
    
                    foreach ($storeRatingIds as $ratingIds) {
                        foreach ($ratingIds as $ratingId) {
                            if ($ratingId) {
                                $this->_ratingIds[$ratingId] = $ratingId;
                            }
                        }
                    }
                }
            }
        }
        
        return $this->_ratingIds;
    }
    
    /**
     * @return array
     */
    protected function getStoreIds()
    {
        /** @var MageWorkshop_DetailedReview_Model_Review $review */
        $review = Mage::registry('review_data');
        if ($review && $review->getId()) {
            return $review->getData('stores');
        }
        $stores = Mage::app()->getRequest()->getParam('select_stores') ?: Mage::app()->getRequest()->getParam('stores');
        
        return is_array($stores) && !empty($stores) ? explode(',', reset($stores)) : array((int) Mage::app()->getStore()->getStoreId());
    }
}

