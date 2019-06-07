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

/**
 * Class MageWorkshop_DRCategoryRatings_Block_Adminhtml_Review_Rating_Detailed
 * @method setIndependentMode(bool $bool)
 * @method bool getIndependentMode()
 * @method setIsIndependentMode(bool $bool)
 * @method bool getIsIndependentMode()
 */
class MageWorkshop_DRCategoryRatings_Block_Adminhtml_Review_Rating_Detailed extends Mage_Adminhtml_Block_Review_Rating_Detailed
{
    protected $_ratingCollection;
    
    public function getRating()
    {
        if (!Mage::getStoreConfig('drcategoryratings/settings/enable')) {
            return parent::getRating();
        }
        
        if( !$this->_ratingCollection ) {
            if( $data = Mage::registry('review_data') ) {
                $this->_ratingCollection = Mage::getModel('rating/rating')
                    ->getResourceCollection();
    
                if ($productId = $data['entity_pk_value']) {
                    $this->addRatingFilter($productId);
                }
    
                $this->_ratingCollection->addEntityFilter('product')
                    ->setPositionOrder()
                    ->load()
                    ->addOptionToItems();
                
                $this->_voteCollection = Mage::getModel('rating/rating_option_vote')
                    ->getResourceCollection()
                    ->setReviewFilter($this->getReviewId())
                    ->addOptionInfo()
                    ->load()
                    ->addRatingOptions();
                
            } elseif (!$this->getIsIndependentMode()) {
                $this->_ratingCollection = Mage::getModel('rating/rating')
                    ->getResourceCollection()
                    ->addEntityFilter('product')
                    ->setStoreFilter(Mage::app()->getDefaultStoreView()->getId())
                    ->setPositionOrder()
                    ->load()
                    ->addOptionToItems();
            } else {
    
                $this->_ratingCollection = Mage::getModel('rating/rating')
                    ->getResourceCollection();
                
                if ($productId = Mage::getSingleton('admin/session')->getData('dr_product_id')) {
                    $this->addRatingFilter($productId);
                }
    
                $this->_ratingCollection->addEntityFilter('product')
                    ->setPositionOrder()
                    ->load()
                    ->addOptionToItems();
                if((int)($this->getRequest()->getParam('id'))){
                    $this->_voteCollection = Mage::getModel('rating/rating_option_vote')
                        ->getResourceCollection()
                        ->setReviewFilter((int)($this->getRequest()->getParam('id')))
                        ->addOptionInfo()
                        ->load()
                        ->addRatingOptions();
                }
            }
        }
        return $this->_ratingCollection;
    }
    
    /**
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    protected function getRatingByProduct($product)
    {
        $drCategoryRatings = Mage::getModel('drcategoryratings/rating');
        $drCategoryRatings->setProduct($product);
        return $drCategoryRatings->getRatingIds();
    }
    
    protected function addRatingFilter($productId) {
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product')->load($productId);
        $this->_ratingCollection->addFieldToFilter('main_table.rating_id', array('in'=> $this->getRatingByProduct($product)));
    }
}
