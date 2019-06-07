<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_ReviewWall
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/**
 * Class MageWorkshop_ReviewWall_Block_Widget_Part_SocialShare_Twitter
 *
 * @method MageWorkshop_DetailedReview_Model_Review getReview()
 * @method $this setReview(MageWorkshop_DetailedReview_Model_Review $review)
 * @method string getShareUrl($url)
 * @method $this setShareUrl(string $url)
 */
class MageWorkshop_ReviewWall_Block_Widget_Part_SocialShare_Twitter extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        $this->setTemplate('mageworkshop/reviewwall/widget/part/socialShare/twitter.phtml');
        parent::_construct();
    }
}
