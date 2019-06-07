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
 * Class MageWorkshop_CommentOnReview_Model_System_Config_Source_Comment_MaxSymbols
 */
class MageWorkshop_CommentOnReview_Model_System_Config_Source_Comment_MaxSymbols
{
    /**
     * Set option for field "Maximum Count Of Reply Symbols"
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 250, 'label' => 250),
            array('value' => 500, 'label' => 500),
            array('value' => 1000, 'label' => 1000),
            array('value' => 1500, 'label' => 1500),
            array('value' => 2000, 'label' => 2000)
        );
    }
}
