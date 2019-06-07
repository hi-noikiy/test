<?php

/**
 * Class MageWorkshop_DetailedReview_Block_Adminhtml_Notification_Message
 */
class MageWorkshop_DetailedReview_Block_Adminhtml_Notification_Message extends Mage_Adminhtml_Block_Template
{
    /**
     * Get DetailedReview admin URL
     * 
     * @return string
     */
    public function getDetailedReviewAdminUrl()
    {
        return $this->getUrl('adminhtml/system_config/edit', array('section' => 'detailedreview'));;
    }
}