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
 * Class MageWorkshop_CommentOnReview_Block_Reply
 *
 * @method Mage_Review_Model_Review getCurrentItem()
 * @method Varien_Data_Collection_Db getReviewCollection()
 */
class MageWorkshop_CommentOnReview_Block_Reply extends Mage_Core_Block_Template
{
    /** @var MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection $_repliesCollection */
    protected $_repliesByReview = array();

    /**
     * @return array
     */
    public function getReviewReplies()
    {
        if ($review = $this->getCurrentItem()) {
            $items = $this->getAllReplies();
            return isset($items[$review->getId()])
                ? $items[$review->getId()]
                : array();
        }
        return array();
    }

    /**
     * Get replies for all reviews collection at once so we do not need to load replies for each entry
     *
     * @return array
     */
    public function getAllReplies()
    {
        /** @var Mage_Rating_Model_Mysql4_Rating_Collection $reviewCollection */
        $reviewCollection = $this->getReviewCollection();
        $reviewCollection->getSelect()->reset(Zend_Db_Select::HAVING);
        $reviewIds = $reviewCollection->getAllIds();
        $hash = md5(implode(',', $reviewIds));

        if (!isset($this->_repliesByReview[$hash])) {
            $repliesByReview = array();
            $review = $this->getCurrentItem();

            $repliesCollection = Mage::getResourceModel('review/review_collection');

            $repliesCollection
                ->addFieldToFilter('entity_pk_value', array('in' => $reviewIds))
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->addFieldToFilter('entity_id', $review->getEntityIdByCode('review'));

            $repliesCollection->addHelpfulInfo();

            $repliesCollection->getSelect()
                ->order(sprintf('created_at %s', Varien_Data_Collection_Db::SORT_ORDER_ASC));


            foreach ($repliesCollection as $reply) {
                if (!isset($repliesByReview[$reply->getEntityPkValue()])) {
                    $repliesByReview[$reply->getEntityPkValue()] = array();
                }

                $repliesByReview[$reply->getEntityPkValue()][] = $reply;
            }

            $this->_repliesByReview[$hash] = $repliesByReview;
        }
        return $this->_repliesByReview[$hash];
    }
}
