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
 * Class MageWorkshop_DetailedReview_Block_Review_Form_Rating
 */
class MageWorkshop_DetailedReview_Block_Review_Form_Rating extends Mage_Core_Block_Template
{

    protected $_ratingCollection;
    /**
     * Rewrite Mage_Review_Block_Form::getRatings
     *
     * Order by rating_code was added
     *
     * @return Mage_Rating_Model_Resource_Rating_Collection
     */
    public function getRatings()
    {
        $ratingCollection = $this->_getRatingCollection();
        $ratingCollection->addOptionToItems();

        return $ratingCollection;
    }

    /**
     * @return Mage_Rating_Model_Resource_Rating_Collection
     */
    protected function _getRatingCollection()
    {
        if (!$this->_ratingCollection) {
            $storeId = Mage::app()->getStore()->getId();
            $ratingCollection = Mage::getModel('rating/rating')
                ->getResourceCollection()
                ->addEntityFilter('product')
                ->setOrder('position', Varien_Data_Collection::SORT_ORDER_ASC)
                ->setOrder('rating_code', Varien_Data_Collection::SORT_ORDER_ASC)
                ->addRatingPerStoreName($storeId)
                ->setStoreFilter($storeId);

            $this->_ratingCollection = $ratingCollection;
        }

        return $this->_ratingCollection;
    }
}
