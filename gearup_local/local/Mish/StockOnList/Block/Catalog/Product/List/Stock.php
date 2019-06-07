<?php
/**
 * List Stock View
 */

class Mish_StockOnList_Block_Catalog_Product_List_Stock extends Mage_Catalog_Block_Product_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('stockonlist/catalog/product/list/stock.phtml');
    }

    public function getQty()
    {
        return (int) $this->getProduct()->getQty();
    }
}