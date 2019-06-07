<?php

/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_CommentOnReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/**
 * Class MageWorkshop_CommentOnReview_Helper_JSCSSManager
 */

class MageWorkshop_CommentOnReview_Helper_JSCSSManager extends MageWorkshop_Core_Helper_Abstract
{

    protected $commentOnReviewJS = 'mageworkshop/commentonreview/commentonreview_ver_%s%s.js';

    public function commentOnReviewJS()
    {
        return $this->builder($this->commentOnReviewJS);
    }
}