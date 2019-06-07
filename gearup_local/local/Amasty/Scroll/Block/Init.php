<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Scroll
 */

class Amasty_Scroll_Block_Init extends Mage_Core_Block_Template
{
    protected function _prepareLayout() 
    {
        return $this;
    }

    public function getTotalPages()
    {
        $layout = $this->getLayout();

        $page = Mage::helper('amscroll')->findProductList($layout);
        if (!$page) {
            return 0;
        }

        return (int)(Mage::getStoreConfig('catalog/frontend/' . $page->getMode() . '_per_page'));
    }
}