<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Scroll
 */


class Amasty_Scroll_Block_Catalog_Pager extends Mage_Core_Block_Template
{
    /**
     * @var array
     */
    private $possibleNames = array(
        'product_list_toolbar',
        'amlanding_product_list_toolbar',
        'ambrands_product_list_toolbar'
    );

    /**
     * @return int
     */
    public function getLastPageNum()
    {
        $lastPage = 1;

        $productListToolbar = $this->getProductListToolbar();
        if ($productListToolbar && $productListToolbar->getCollection()) {
            $lastPage = (int)$productListToolbar->getLastPageNum();
        }

        return $lastPage;
    }

    /**
     * @return Mage_Catalog_Block_Product_List_Toolbar|null
     */
    private function getProductListToolbar()
    {
        $productListToolbar = null;
        foreach ($this->possibleNames as $nameInLayout) {
            $productListToolbar = $this->getLayout()->getBlock($nameInLayout);
            if ($productListToolbar) {
                break;
            }
        }

        return $productListToolbar;
    }
}
