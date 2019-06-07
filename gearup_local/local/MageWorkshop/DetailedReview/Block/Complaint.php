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
 * Class MageWorkshop_DetailedReview_Block_Complaint
 */
class MageWorkshop_DetailedReview_Block_Complaint extends Mage_Core_Block_Template
{
    const STATUS_ID = 'status_id';

    public function getComplaint()
    {
        $result = array();
        $complaintTypeCollection = Mage::getModel('detailedreview/complaintType')->getCollection();
        $complaintTypeCollection->addFieldToFilter(self::STATUS_ID, MageWorkshop_DetailedReview_Model_Source_Common_Status::STATUS_ENABLED);
        foreach($complaintTypeCollection as $complaintType) {
            $result[] = $complaintType;
        }
        return $result;
    }
}