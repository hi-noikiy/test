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
 * Class MageWorkshop_ReviewWall_Helper_JSCSSManager
 */

class MageWorkshop_ReviewWall_Helper_JSCSSManager extends MageWorkshop_Core_Helper_Abstract
{

    protected $jqueryWidgetJS     = 'mageworkshop/reviewwall/jquery-ui-widget_ver_%s%s.js';
    protected $dotJS              = 'mageworkshop/reviewwall/doT_ver_%s%s.js';
    protected $masonryJS          = 'mageworkshop/reviewwall/masonry/masonry.pkgd_ver_%s%s.js';
    protected $imageloadJS        = 'mageworkshop/reviewwall/imagesloaded-master/imagesloaded.pkgd_ver_%s%s.js';
    protected $reviewallJS        = 'mageworkshop/reviewwall/reviewwall_ver_%s%s.js';

    public function jqueryWidgetJS()
    {
        return $this->builder($this->jqueryWidgetJS);
    }

    public function dotJS()
    {
        return $this->builder($this->dotJS);
    }

    public function masonryJS()
    {
        return $this->builder($this->masonryJS);
    }

    public function imageloadJS()
    {
        return $this->builder($this->imageloadJS);
    }

    public function reviewallJS()
    {
        return $this->builder($this->reviewallJS);
    }

    /**
     * @param Mage_Page_Block_Html_Head $head
     * @return Mage_Page_Block_Html_Head mixed
     */
    public function getCSSandJS($head)
    {
        if (!MageWorkshop_DetailedReview_Helper_Config::isDetailedReviewEnabled()) {
           return $head;
        }
        $head->addCss('css/mageworkshop/reviewwall/reviewwall_ver_3.1.5.css');
        $head->addCss('css/detailedreview/reviewshare_ver_3.1.5.css');
        $head->addCss('css/detailedreview/pnotify.custom.min_ver_3.1.5.css');

        /** @var MageWorkshop_DetailedReview_Helper_JSCSSManager $detailedreviewHelper */
        $detailedreviewHelper = Mage::helper('detailedreview/jSCSSManager');

        /** @var MageWorkshop_Core_Helper_JSCSSManager $drCoreHelper */
        $drCoreHelper = Mage::helper('drcore/jSCSSManager');

        /** @var MageWorkshop_DetailedReview_Helper_Config $configHelper */
        $configHelper = Mage::helper('detailedreview/config');

        $head->addJs($drCoreHelper->startDrWrapper(), 'data-group="dr-js001"');

        if ($configHelper::isDRjQuery()) {
            $head->addJs($detailedreviewHelper->jQueryJS(), 'data-group="dr-js002"');
        }

        $head->addJs($this->jqueryWidgetJS(), 'data-group="dr-js003"');
        $head->addJs($this->masonryJS(), 'data-group="dr-js003"');
        $head->addJs($this->imageloadJS(), 'data-group="dr-js003"');
        $head->addJs($detailedreviewHelper->pnotifyJS(), 'data-group="dr-js003"');

        $head->addJs($drCoreHelper->endDrWrapper(), 'data-group="dr-js004"');

        $head->addJs($this->dotJS(), 'data-group="dr-js007"');

        $head->addJs($this->reviewallJS(), 'data-group="dr-js010"');
        $head->addJs($detailedreviewHelper->reviewShareJS(), 'data-group="dr-js010"');
        $head->addJs($detailedreviewHelper->bitlyJS(), 'data-group="dr-js010"');
        $head->addJs($detailedreviewHelper->fbShareJS(), 'data-group="dr-js010"');

        return $head;
    }
}