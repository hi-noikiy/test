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
 * Class MageWorkshop_DetailedReview_Model_Mysql4_ComplaintType
 */
class MageWorkshop_DetailedReview_Model_Mysql4_ComplaintType extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('detailedreview/complaint_type', 'entity_id');
    }
}