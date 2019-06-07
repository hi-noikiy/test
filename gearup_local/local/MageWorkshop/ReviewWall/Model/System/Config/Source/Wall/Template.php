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
 * Class MageWorkshop_ReviewWall_Model_System_Config_Source_Wall_Template
 */
class MageWorkshop_ReviewWall_Model_System_Config_Source_Wall_Template
{
    /**
     * Set option for field "Wall template"
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 1, 'label'=>Mage::helper('reviewwall')->__('All Reviews')),
            array('value' => 2, 'label'=>Mage::helper('reviewwall')->__('Reviews with image')),
        );
    }

}
