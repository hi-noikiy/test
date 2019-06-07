<?php

/**
 * Class MageWorkshop_CommentOnReview_Block_Adminhtml_Notification_Message
 */
class MageWorkshop_CommentOnReview_Block_Adminhtml_Notification_Message extends Mage_Adminhtml_Block_Template
{
    /**
     * Get CommentOnReview admin URL
     * 
     * @return string
     */
    public function getCommentOnReviewAdminUrl()
    {
        return $this->getUrl('adminhtml/system_config/edit', array('section' => 'mageworkshop_commentonreview'));;
    }
}