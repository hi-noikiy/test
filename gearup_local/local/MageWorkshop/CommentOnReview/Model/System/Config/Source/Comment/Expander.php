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
 * Class MageWorkshop_CommentOnReview_Model_System_Config_Source_Comment_Expander
 */
class MageWorkshop_CommentOnReview_Model_System_Config_Source_Comment_Expander
{
    /**
     * Set option for field "Expander Reply"
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label' => 1),
            array('value' => 2, 'label' => 2),
            array('value' => 5, 'label' => 5),
            array('value' => 10, 'label' => 10),
        );
    }
}