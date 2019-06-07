<?php
/**
 * MageWorx
 * MageWorx SeoBase Extension
 * 
 * @category   MageWorx
 * @package    MageWorx_SeoBase
 * @copyright  Copyright (c) 2015 MageWorx (http://www.mageworx.com/)
 */


class MageWorx_SeoBase_Block_Cms_Page extends Mage_Cms_Block_Page
{

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $page = $this->getPage();
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->setRobots($page->getMetaRobots());
        }
    }

}
