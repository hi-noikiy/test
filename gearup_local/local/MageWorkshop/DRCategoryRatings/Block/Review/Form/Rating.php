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
 * Class MageWorkshop_DRCategoryRatings_Block_Review_Form_Rating
 */
class MageWorkshop_DRCategoryRatings_Block_Review_Form_Rating extends MageWorkshop_DetailedReview_Block_Review_Form_Rating
{
    /**
     * Rewrite Mage_Review_Block_Form::getRatings
     *
     * Order by rating_code was added
     *
     * @return Mage_Rating_Model_Resource_Rating_Collection
     */
    public function getRatings()
    {
        $ids = array();
        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::registry('current_product');
        
        if ($product && $product->getId()) {
            $drCategoryRatings = Mage::getModel('drcategoryratings/rating');
            $drCategoryRatings->setProduct($product);
            $ids = $drCategoryRatings->getRatingIds();
        }

        /** @var Mage_Rating_Model_Resource_Rating_Collection $ratingCollection */
        $ratingCollection = $this->_getRatingCollection();
        $ratingCollection
            ->addFieldToFilter('main_table.rating_id', array('in'=> $ids))
            ->addOptionToItems();
        
        return $ratingCollection;
    }
}
