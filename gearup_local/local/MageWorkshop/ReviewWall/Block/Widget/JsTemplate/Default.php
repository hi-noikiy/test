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
 * Class MageWorkshop_ReviewWall_Block_Widget_JsTemplate_Default
 */
class  MageWorkshop_ReviewWall_Block_Widget_JsTemplate_Default extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        $this->setTemplate(Mage::helper('reviewwall')->getJsTemplate());

        parent::_construct();
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        $html = parent::_toHtml();
        return trim(preg_replace('/\s+/', ' ', $html));
    }
}
