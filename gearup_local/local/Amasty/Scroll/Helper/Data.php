<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Scroll
 */
class Amasty_Scroll_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getModuleConfig($key)
    {
        return Mage::getStoreConfig('amscroll/' . $key);
    }
    
    public function findProductList($layout)
    {
        $page = $layout->getBlock('product_list');
        if (!$page) {
            $page = $layout->getBlock('category.products');
        }

        if (!$page) {
            $page = $layout->getBlock('search_result_list');
        }

        if (!$page) {
            $page = $layout->getBlock('catalogsearch_advanced_result');
        }

        return $page;
    }
    
    public function isEnabled()
    {
        if ($this->getModuleConfig('general/loading') == 'none') {
            return false;
        }

        $routes = array(
            'catalog',
            'catalogsearch',
            'tag',
            'amshopby',
            'amlanding',
            'ambrands',
            'cms',
        );

        return in_array(Mage::app()->getRequest()->getRouteName(), $routes);
    }
}
