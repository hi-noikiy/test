<?php

/**
 * Class MageWorkshop_CommentOnReview_Block_Reply_Item
 */
class MageWorkshop_CommentOnReview_Block_Reply_Item extends Mage_Core_Block_Template
{
    /** @var null $_newReply */
    protected $_newReply;

    /**
     * Get new reply
     *
     * @return null|MageWorkshop_DetailedReview_Model_Review
     */
    public function getNewReply()
    {
        return $this->_newReply;
    }

    /**
     * Set new reply
     *
     * @param Mage_Review_Model_Resource_Review_Collection|MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection $newReply
     */
    public function setNewReply($newReply)
    {
        $this->_newReply = $newReply;
    }
}