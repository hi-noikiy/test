<?php

class Hatimeria_AttributeBrowser_Block_Product_List extends Mage_Catalog_Block_Product_List
{
    protected function _getProductCollection()
    {
        if (!isset($this->_productCollection)) {

            $collection = Mage::getModel('catalog/product')->getCollection();
            Mage::getModel('cataloginventory/stock')->addInStockFilterToCollection($collection);
            Mage::getModel('catalog/layer')->prepareProductCollection($collection);

            $model = Mage::getSingleton('attributebrowser/list');
            $attr = $model->getCurrentAttribute();
            $item = $model->getCurrentItem();

            $collection->addAttributeToFilter($attr['code'], $item['id']);
            $this->_productCollection = $collection;

            $this->_productCollection = $collection;
        }

        return $this->_productCollection;
    }
}