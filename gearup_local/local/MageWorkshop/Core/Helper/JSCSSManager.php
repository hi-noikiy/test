<?php

/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_Core
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */

/**
 * Class MageWorkshop_Core_Helper_JSCSSManager
 */
class MageWorkshop_Core_Helper_JSCSSManager extends MageWorkshop_Core_Helper_Abstract
{

    /** @var string $mageworkshopHelper */
    protected $mageworkshopHelper = 'mageworkshop/core/mageworkshopHelper_ver_%s%s.js';
    protected $drJquery           = 'mageworkshop/core/dr_jquery_ver_%s%s.js';
    protected $startDrJsWrapper   = 'mageworkshop/core/start_dr_wrapper_ver_%s%s.js';
    protected $endDrJsWrapper     = 'mageworkshop/core/end_dr_wrapper_ver_%s%s.js';

    /**
     * @return string
     */
    public function mageworkshopHelper()
    {
        return $this->builder($this->mageworkshopHelper);
    }

    /**
     * @return string
     */
    public function drJquery()
    {
        return $this->builder($this->drJquery);
    }

    /**
     * @return string
     */
    public function startDrWrapper()
    {
        return $this->builder($this->startDrJsWrapper);
    }

    /**
     * @return string
     */
    public function endDrWrapper()
    {
        return $this->builder($this->endDrJsWrapper);
    }
}