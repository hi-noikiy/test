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
 * Class MageWorkshop_ReviewWall_Block_Widget_Part_EmailShare
 */
class MageWorkshop_ReviewWall_Block_Widget_Part_EmailShare extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        $this->setTemplate('mageworkshop/reviewwall/widget/part/emailShare.phtml');
        parent::_construct();
    }
}
