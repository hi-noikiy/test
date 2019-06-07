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
 * Class MageWorkshop_ReviewWall_Model_System_Config_Source_Wall_Pages
 */
class MageWorkshop_ReviewWall_Model_System_Config_Source_Wall_Pages
{
    /**
     * Set option for field "Reviews on page"
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value'=>20, 'label'=>Mage::helper('reviewwall')->__('20')),
            array('value'=>30, 'label'=>Mage::helper('reviewwall')->__('30')),
            array('value'=>50, 'label'=>Mage::helper('reviewwall')->__('50')),
            array('value'=>100, 'label'=>Mage::helper('reviewwall')->__('100')),
        );
    }

}
