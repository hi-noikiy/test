<?php
/**
 * MageWorx
 * MageWorx XSitemap Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_XSitemap
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */

if ((string) Mage::getConfig()->getModuleConfig('MageWorx_SeoBase')->active == 'true') {

    class MageWorx_XSitemap_Block_Adminhtml_Xsitemap_Cms_Page_Edit_Tab_Meta_Abstract extends MageWorx_SeoBase_Block_Adminhtml_Cms_Page_Edit_Tab_Meta
    {

    }

}
else {

    class MageWorx_XSitemap_Block_Adminhtml_Xsitemap_Cms_Page_Edit_Tab_Meta_Abstract extends Mage_Adminhtml_Block_Cms_Page_Edit_Tab_Meta
    {

    }

}
