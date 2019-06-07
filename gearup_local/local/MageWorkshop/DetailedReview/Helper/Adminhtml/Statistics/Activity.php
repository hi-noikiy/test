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
class MageWorkshop_DetailedReview_Helper_Adminhtml_Statistics_Activity extends Mage_Adminhtml_Helper_Dashboard_Abstract
{
    /**
     * Init items collection for statistics chart
     */
    protected function _initCollection()
    {
        $this->_collection = Mage::getResourceSingleton('detailedreview/review_reports_activity')
            ->getActivity($this->getParam('period'));
    }
}
