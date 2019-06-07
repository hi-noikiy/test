<?php
/**
 * MageWorx
 * MageWorx SeoMarkup Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_SeoMarkup
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */



class MageWorx_SeoMarkup_Model_System_Config_Source_ActionName
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'no', 'label' => Mage::helper('catalog')->__('No')),
            array('value' => 'source_code', 'label' => Mage::helper('seomarkup')->__('Show in Source Code of Page')),
        );
    }
}
