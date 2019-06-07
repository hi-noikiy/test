<?php
/**
 * MageWorx
 * MageWorx SeoMarkup Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_SeoMarkup
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */



class MageWorx_SeoMarkup_Model_System_Config_Source_Richsnippets
{
    public function toOptionArray()
    {
        return array(
            array('value' => '0', 'label' => Mage::helper('seomarkup')->__('No')),
            array('value' => '1', 'label' => Mage::helper('seomarkup')->__('Yes')),
            array('value' => '2', 'label' => Mage::helper('seomarkup')->__('For Breadcrumbs Only')),
        );
    }

}
