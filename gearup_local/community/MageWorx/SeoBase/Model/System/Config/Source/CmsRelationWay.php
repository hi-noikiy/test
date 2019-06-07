<?php
/**
 * MageWorx
 * MageWorx SeoBase Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoBase
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */


class MageWorx_SeoBase_Model_System_Config_Source_CmsRelationWay
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'0', 'label'=>Mage::helper('core')->__('By ID')),
            array('value'=>'1', 'label'=>Mage::helper('core')->__('By URL Key')),
            array('value'=>'2', 'label'=>Mage::helper('core')->__('By Hreflang Key')),
        );
    }
}