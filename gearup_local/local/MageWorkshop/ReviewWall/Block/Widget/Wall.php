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
 * Class MageWorkshop_ReviewWall_Block_Widget_Wall
 */
class MageWorkshop_ReviewWall_Block_Widget_Wall extends Mage_Core_Block_Abstract
    implements Mage_Widget_Block_Interface
{
    /**
     * Prepare Reviews Wall html output
     *
     * @return mixed
     */
    protected function _toHtml()
    {
        if (!MageWorkshop_DetailedReview_Helper_Config::isDetailedReviewEnabled()) {
            $this->getMessagesBlock()->addNotice($this->__('It looks like Detailed Review extension is not enabled. Please enable it for correct work of Review Wall module'));

            return;
        }
        $block = Mage::getSingleton('core/layout')
            ->createBlock('core/template')
            ->setTemplate(Mage::helper('reviewwall')->getTemplate());

        /** @var Mage_Core_Block_Abstract $block */
        $block
            ->setChild('searchForm', $this->getLayout()->createBlock('reviewwall/widget_part_searchForm'))
            ->setChild('totalRating', $this->getLayout()->createBlock('reviewwall/widget_part_totalRating'))
            ->setChild('socialShare', $this->getLayout()->createBlock('reviewwall/widget_part_socialShare'))
            ->setChild('emailShare', $this->getLayout()->createBlock('reviewwall/widget_part_emailShare'))
            ->setChild('helpful', $this->getLayout()->createBlock('reviewwall/widget_part_helpful'))
            ->setChild('published', $this->getLayout()->createBlock('reviewwall/widget_part_published'))
            ->setChild('js', $this->getLayout()->createBlock('reviewwall/widget_part_js'));

        $block
            ->setChild('defaultTemplate', $this->getLayout()->createBlock('reviewwall/widget_jsTemplate_default'));

        return $block->toHtml();
    }

    /**
     * Preparing global layout
     *
     * You can redefine this method in child classes for changing layout
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout() {
        /** @var Mage_Page_Block_Html_Head $head */
        if ($head = $this->getLayout()->getBlock('head')) {
            /** @var MageWorkshop_ReviewWall_Helper_JSCSSManager $helper */
            $helper = Mage::helper('reviewwall/jSCSSManager');
            $helper->getCSSandJS($head);
        }

        return parent::_prepareLayout();
    }
}
