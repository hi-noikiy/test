<?php
/**
 * MageWorx
 * MageWorx XSitemap Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_XSitemap
 * @copyright  Copyright (c) 2017 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_XSitemap_Model_Adminhtml_System_Config_Source_Xsitemap_Sortorder_Url
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'default', 'label' => Mage::helper('mageworx_xsitemap')->__('Default')),
            array('value' => 'short', 'label' => Mage::helper('mageworx_xsitemap')->__('Short')),
            array('value' => 'long', 'label' => Mage::helper('mageworx_xsitemap')->__('Long'))
        );
    }

}
