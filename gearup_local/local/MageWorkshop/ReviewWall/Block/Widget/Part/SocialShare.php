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
 * Class MageWorkshop_ReviewWall_Block_Widget_Part_SocialShare
 */
class MageWorkshop_ReviewWall_Block_Widget_Part_SocialShare extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        $this->setTemplate('mageworkshop/reviewwall/widget/part/socialShare.phtml');
        $this->setChild('facebook', Mage::app()->getLayout()->createBlock('reviewwall/widget_part_socialShare_facebook'));
        $this->setChild('twitter', Mage::app()->getLayout()->createBlock('reviewwall/widget_part_socialShare_twitter'));
        $this->setChild('pinterest', Mage::app()->getLayout()->createBlock('reviewwall/widget_part_socialShare_pinterest'));
        $this->setChild('email', Mage::app()->getLayout()->createBlock('reviewwall/widget_part_socialShare_email'));
        parent::_construct();
    }
}
