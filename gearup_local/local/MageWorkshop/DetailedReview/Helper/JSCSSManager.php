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
 * Class MageWorkshop_DetailedReview_Helper_JSCSSManager
 */

class MageWorkshop_DetailedReview_Helper_JSCSSManager extends MageWorkshop_Core_Helper_Abstract
{

    protected $jQueryJS                 = 'detailedreview/jquery_ver_%s%s.js';
    protected $detailedreviewFullJS     = 'detailedreview/dr.full.min_ver_%s%s.js';
    protected $detailedreviewJS         = 'detailedreview/detailedreview_ver_%s%s.js';
    protected $reviewshareJS            = 'detailedreview/reviewshare_ver_%s%s.js';
    protected $tamingselectJS           = 'detailedreview/tamingselect_ver_%s%s.js';
    protected $placeholderJS            = 'detailedreview/placeholder_ver_%s%s.js';
    protected $selectivizrJS            = 'detailedreview/selectivizr_ver_%s%s.js';
    protected $ajaxLoadReviewJS         = 'detailedreview/ajaxLoadReview_ver_%s%s.js';
    protected $jqueryBrowserJS          = 'detailedreview/jquery.browser_ver_%s%s.js';
    protected $drResponseJS             = 'detailedreview/drResponse_ver_%s%s.js';
    protected $fbShareJS                = 'detailedreview/fbShare_ver_%s%s.js';
    protected $momentJS                 = 'detailedreview/moment_ver_%s%s.js';
    protected $bitlyJS                  = 'detailedreview/bitly_ver_%s%s.js';
    protected $jstzJS                   = 'detailedreview/jstz_ver_%s%s.js';
    protected $pnotifyJS                = 'detailedreview/pnotify.custom_ver_%s%s.js';
    protected $fancyboxJS               = 'detailedreview/jquery.fancybox_ver_%s%s.js';
    protected $noUiSliderJS             = 'detailedreview/nouislider_ver_%s%s.js';
    protected $jqueryColorJS            = 'detailedreview/jquery.color_ver_%s%s.js';
    protected $jqueryEasingJS           = 'detailedreview/jquery.easing_ver_%s%s.js';
    protected $jqueryFormJS             = 'detailedreview/jquery.form_ver_%s%s.js';
    protected $jquerySPYJS              = 'detailedreview/jquery.spy_ver_%s%s.js';
    protected $adminhtmlJS              = 'detailedreview/adminhtml_ver_%s%s.js';
    protected $adminhtmlReviewEditJS    = 'detailedreview/adminhtml_review_edit_ver_%s%s.js';

    public function jQueryJS()
    {
        return $this->builder($this->jQueryJS);
    }

    public function jqueryColorJS()
    {
        return $this->builder($this->jqueryColorJS);
    }

    public function jqueryEasingJS()
    {
        return $this->builder($this->jqueryEasingJS);
    }

    public function jqueryFormJS()
    {
        return $this->builder($this->jqueryFormJS);
    }

    public function jquerySPYJS()
    {
        return $this->builder($this->jquerySPYJS);
    }

    public function detailedReviewJS()
    {
        return $this->builder($this->detailedreviewJS);
    }

    public function reviewShareJS()
    {
        return $this->builder($this->reviewshareJS);
    }

    public function tamingselectJS()
    {
        return $this->builder($this->tamingselectJS);
    }

    public function placeholderJS()
    {
        return $this->builder($this->placeholderJS);
    }

    public function selectivizrJS()
    {
        return $this->builder($this->selectivizrJS);
    }

    public function ajaxLoadReviewJS()
    {
        return $this->builder($this->ajaxLoadReviewJS);
    }

    public function jqueryBrowserJS()
    {
        return $this->builder($this->jqueryBrowserJS);
    }

    public function drResponseJS()
    {
        return $this->builder($this->drResponseJS);
    }

    public function fbShareJS()
    {
        return $this->builder($this->fbShareJS);
    }

    public function momentJS()
    {
        return $this->builder($this->momentJS);
    }

    public function bitlyJS()
    {
        return $this->builder($this->bitlyJS);
    }

    public function jstzJS()
    {
        return $this->builder($this->jstzJS);
    }

    public function pnotifyJS()
    {
        return $this->builder($this->pnotifyJS);
    }

    public function fancyboxJS()
    {
        return $this->builder($this->fancyboxJS);
    }

    public function noUiSliderJS()
    {
        return $this->builder($this->noUiSliderJS);
    }

    public function adminhtmlJS()
    {
        return $this->builder($this->adminhtmlJS);
    }

    public function adminhtmlReviewEditJS()
    {
        return $this->builder($this->adminhtmlReviewEditJS);
    }
}