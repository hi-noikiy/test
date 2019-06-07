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
class MageWorkshop_DetailedReview_Block_Review_Form extends Mage_Review_Block_Form
{
    /**
     * @var Mage_Catalog_Model_Category $_category
     */
    protected $_category;
    protected $_prosConsCollection = array();

    /**
     * @inherit
     */
    protected function _toHtml()
    {
        Mage::helper('detailedreview')->applyTheme($this);
        return parent::_toHtml();
    }

    /**
     * @param string $entityType
     * @return Varien_Data_Collection_Db
     */
    public function getProsConsCollection($entityType)
    {
        if(!array_key_exists($entityType, $this->_prosConsCollection)) {
            $class = MageWorkshop_DetailedReview_Model_Source_EntityType::getClassNameByType($entityType);
            if ($this->_category) {
                $ratings = $this->_category->getData($class);
            }
            if(empty($ratings)) {
                /** @var MageWorkshop_DetailedReview_Helper_Data $helper */
                $helper = Mage::helper('detailedreview');
                $this->_category = $helper->getCategoryWithConfig(null, 'use_parent_proscons_settings');
                $ratings = $this->_category->getData($class);
            }
            $collection = Mage::getModel('detailedreview/review_proscons')->getCollection()
                ->setType($entityType)
                ->addFieldToFilter('status', MageWorkshop_DetailedReview_Model_Source_Common_Status::STATUS_ENABLED)
                ->addStoreFilter();
            $collection->addFieldToFilter('main_table.entity_id', array(
                'in' => explode(',', $ratings)
            ));
           $this->_prosConsCollection[$entityType] = $collection;
        }
        Mage::dispatchEvent('detailedreview_review_prosconscollection', array('collection' => $this->_prosConsCollection[$entityType]));
        return $this->_prosConsCollection[$entityType];
    }

    public function getAction()
    {
        /** @var Mage_Wishlist_Model_Item $wishlistItem */
        $wishlistItem = Mage::registry('wishlist_item');
        $productId = $wishlistItem ? $wishlistItem->getProductId() : Mage::app()->getRequest()->getParam('id', false);

        return Mage::getUrl('review/product/post', array('id' => $productId, '_secure' => $this->_isSecure()));
    }
    
    /**
     * Checks is request Url is secure
     * Method was rewritten for compatibility with Magento versions less than 1.9.2.0
     *
     * @return bool
     */
    protected function _isSecure()
    {
        return $this->_getApp()->getFrontController()->getRequest()->isSecure();
    }
    
    /**
     * Retrieve application instance
     * Method was rewritten for compatibility with Magento versions less than 1.8.1.0
     *
     * @return Mage_Core_Model_App
     */
    protected function _getApp()
    {
        return is_null($this->_app) ? Mage::app() : $this->_app;
    }
}
