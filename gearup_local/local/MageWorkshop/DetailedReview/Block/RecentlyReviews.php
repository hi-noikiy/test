<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/**
 * Class MageWorkshop_DetailedReview_Block_RecentlyReviews
 *
 * @method bool|int getIsPerCategory()
 */
class MageWorkshop_DetailedReview_Block_RecentlyReviews extends Mage_Core_Block_Template
{
    /**
     * @var Mage_Review_Model_Resource_Review_Product_Collection $_collection
     */
    protected $_collection;
    
    /**
     * @var string
     */
    protected $_recentReviewsConfig;
    
    
    public function _construct()
    {
        parent::_construct();
        $this->_recentReviewsConfig = MageWorkshop_DetailedReview_Helper_Config::getRecentReviewOption();
    }
    
    /**
     * @return Mage_Review_Model_Resource_Review_Product_Collection
     */
    public function getProductCollection()
    {
        if (is_null($this->_collection)) {
            /** @var Mage_Review_Model_Resource_Review_Product_Collection $collection */
            $collection = Mage::getModel('review/review')->getProductCollection()
                ->addAttributeToSelect('url_key')
                ->addFilter("rt.status_id",array('eq' => 1))
                ->addAttributeToFilter('status', array('in' => Mage::getSingleton('catalog/product_status')->getVisibleStatusIds()))
                ->addAttributeToFilter('visibility', array('in' =>Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds()))
                ->addStoreFilter()
                ->setPageSize(Mage::getStoreConfig('detailedreview/category_options/qty_items'))
                ->setOrder('rt.created_at','DESC');
            
            // $this->getIsPerCategory() check layout settings, they are a priority
            $perCategory = $this->getIsPerCategory() || $this->_recentReviewsConfig === 'in_category';
            
            if (($category = Mage::registry('current_category')) && $perCategory) {
                $collection->addCategoryFilter($category)
                    ->addUrlRewrite($category->getId());
            }

            if (!$this->getIsPerCategory()) {
                $collection->addUrlRewrite();
            }
            $this->_collection = $collection;
        }
        Mage::dispatchEvent('detailedreview_recentlyreviews_productcollection', array('collection' => $this->_collection));
        return $this->_collection;
    }

    /**
     * @inherit
     */
    protected function _beforeToHtml()
    {
        $allowConfigs = array('general', 'in_category');
        
        if (!in_array($this->_recentReviewsConfig, $allowConfigs, true)) {
            $this->setTemplate('');
        }
        
        $this->getProductCollection()
            ->addReviewSummary();
        return parent::_beforeToHtml();
    }
}
