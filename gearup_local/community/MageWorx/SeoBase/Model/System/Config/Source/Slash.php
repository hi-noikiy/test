<?php
/**
 * MageWorx
 * MageWorx SeoBase Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_SeoBase
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */


class MageWorx_SeoBase_Model_System_Config_Source_Slash
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'default', 'label' => Mage::helper('seosuite')->__('Default')),
            array('value' => 'add', 'label' => Mage::helper('seosuite')->__('Add')),
            array('value' => 'crop', 'label' => Mage::helper('seosuite')->__('Crop')),
        );
    }
}
