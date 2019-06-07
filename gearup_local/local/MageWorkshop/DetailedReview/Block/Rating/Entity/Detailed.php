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
 * Class MageWorkshop_DetailedReview_Block_Rating_Entity_Detailed
 *
 * @method MageWorkshop_DetailedReview_Block_Rating_Entity_Detailed setSummary(float $float)
 * @method MageWorkshop_DetailedReview_Block_Rating_Entity_Detailed setCountReviewsWithRating(float)
 */
class MageWorkshop_DetailedReview_Block_Rating_Entity_Detailed extends Mage_Core_Block_Template
{
    protected $_reviewCollections = array();
    protected $_qtyMarks = array();
    protected $_availableSorts = array();
    protected $_avgRating;
    protected $_reviewCount;
    protected $_resource;

    /**
     * @inherit
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('detailedreview/rating/detailed.phtml');
    }

    /**
     * @inherit
     */
    protected function _toHtml()
    {
        /** @var Mage_Wishlist_Model_Item $wishlistItem */
        $wishlistItem = Mage::registry('wishlist_item');
        $entityId = $wishlistItem ? (int) $wishlistItem->getProductId() : (int) Mage::app()->getRequest()->getParam('id', false);
        
        if ($entityId <= 0) {
            return '';
        }
    
        $this->_reviewCount = $this->getReviewCount($entityId);
        if (!$this->_reviewCount) {
            $this->setTemplate('detailedreview/rating/empty.phtml');
            return parent::_toHtml();
        }

        $this->_avgRating = $this->getAvgRating($entityId);
        
        Mage::helper('detailedreview')->applyTheme($this);
        return parent::_toHtml();
    }
    
    /**
     * @param $entityId
     * @return float
     */
    public function getAvgRating($entityId)
    {
        $resource = $this->getResource();
        $readConnection = $resource->getConnection('core_read');
        /** @var Varien_Db_Select $select */
        $select = $readConnection->select()
            ->from(array('rating_aggregated' => $resource->getTableName('rating/rating_vote_aggregated')), array('AVG(percent_approved)'))
            ->join(
                array('rating' => $resource->getTableName('rating/rating')),
                'rating_aggregated.rating_id = rating.rating_id',
                null
            )
            ->join(
                array('rating_entity' => $resource->getTableName('rating/rating_entity')),
                'rating.entity_id = rating_entity.entity_id',
                null
            )
            ->where('rating_aggregated.entity_pk_value = ?', $entityId)
            ->where('rating_aggregated.store_id = ?', Mage::app()->getStore()->getId())
            ->where('rating_entity.entity_code = ?', Mage_Rating_Model_Rating::ENTITY_PRODUCT_CODE);
    
        return round($readConnection->fetchOne($select), 2);
    }
    
    /**
     * @param $entityId
     * @return int
     */
    public function getReviewCount($entityId) {
        $resource = $this->getResource();;
        $readConnection = $resource->getConnection('core_read');
        /** @var Varien_Db_Select $select */
        $select = $readConnection->select()
            ->from(array('r' => $resource->getTableName('review/review')), array('COUNT(*)'))
            ->join(
                array('re' => $resource->getTableName('review/review_entity')),
                'r.entity_id = re.entity_id',
                null
            )
            ->join(
                array('rs' => $resource->getTableName('review/review_store')),
                'r.review_id = rs.review_id',
                null
            )
            ->where('r.entity_pk_value = ?', $entityId)
            ->where('r.status_id = ?', Mage_Review_Model_Review::STATUS_APPROVED)
            ->where('re.entity_code = ?', Mage_Review_Model_Review::ENTITY_PRODUCT_CODE)
            ->where('rs.store_id = ?', Mage::app()->getStore()->getId());
    
        return (int) $readConnection->fetchOne($select);
    }

    /**
     * @param int $range
     * @return mixed
     */
    public function getQtyMarks($range = 0)
    {
        if (!isset($this->_qtyMarks[$range])) {
            $reviewsIds = array();
            /** @var Mage_Review_Model_Review $review */
            foreach ($this->getReviewCollection($range) as $review) {
                $reviewsIds[] = $review->getId();
            }
            $this->_qtyMarks[$range] = Mage::getModel('detailedreview/rating_option_vote')->getQtyMarks($reviewsIds);
        }
        return $this->_qtyMarks[$range];
    }

    /**
     * @param int $range
     * @return mixed
     */
    public function getQtyByRange($range = 0) {
        /** @var MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection $collection */
        $collection = $this->getReviewCollection($range);
        return $collection->count();
    }

    /**
     * @return float
     */
    public function getAverageSizing()
    {
        /** @var MageWorkshop_DetailedReview_Model_Mysql4_Review $resourceReviewModel */
        $resourceReviewModel = Mage::getResourceModel('detailedreview/review');

        return $resourceReviewModel->getAverageSizing();
    }

    /**
     * @param int $range
     * @return mixed
     */
    public function getReviewCollection($range = 0)
    {
        $params = Mage::app()->getRequest()->getParams();
        $range = ($range != 0) ? $range : ((isset($params['st'])) ? $params['st'] : 0);
        if (!isset($this->_reviewCollections[$range])) {
            $reviewCollection = Mage::getSingleton('detailedreview/review')->getReviewsCollection(true, $range);
            $this->_reviewCollections[$range] = $reviewCollection;
        }
        return $this->_reviewCollections[$range];
    }

    /**
     * @return mixed
     */
    public function getCurrentProduct()
    {
        return Mage::registry('current_product');
    }

    /**
     * @param bool $ratingsEnabled
     * @return array
     */
    public function getAvailableSorts($ratingsEnabled)
    {
        $options = Mage::getSingleton('detailedreview/review_sorting')->getAvailableOptions();
        if (!$ratingsEnabled){
            unset($options['rate_desc']);
            unset($options['rate_asc']);
        }
        return $options;
    }

    /**
     * @return string
     */
    public function getCurrentSorting()
    {
        return Mage::getSingleton('detailedreview/review_sorting')->getCurrentSorting();
    }

    /**
     * @return array
     */
    public function getAvailableFilterAttributes()
    {
        $helper = Mage::helper('detailedreview');
        $availableFilterAttributes = array(
            'verified_buyers' => $helper->__('Verified Buyers')
        );

        if ($helper->checkFieldAvailable('image', 'form')) {
            $availableFilterAttributes['images'] = $helper->__('Reviews with Images');
        }
        if ($helper->checkFieldAvailable('video', 'form')) {
            $availableFilterAttributes['video'] = $helper->__('Reviews with Video');
        }
        if ($helper->checkFieldAvailable('response', 'info')) {
            $availableFilterAttributes['admin_response'] = $helper->__('Administration Response');
        }
        $availableFilterAttributes['highest_contributors'] = $helper->__('Highest Contributors');

        return $availableFilterAttributes;
    }

    /**
     * @return array
     */
    public function getAvailableDateRanges()
    {
        $helper = Mage::helper('detailedreview');
        return array(
            1 => $helper->__('My Reviews'),
            2 => $helper->__('Last Week'),
            3 => $helper->__('Last 4 Weeks'),
            4 => $helper->__('Last 6 Months'),
            999 => $helper->__('All Reviews')
        );
    }
    
    /**
     * @return Mage_Core_Model_Abstract|Mage_Core_Model_Resource
     */
    protected function getResource()
    {
        if (!$this->_resource) {
            $this->_resource = Mage::getSingleton('core/resource');
        }
        
        return $this->_resource;
    }
}
