<?php

/**
 * List
 */
class Mish_Catalog_Block_Product_List extends Mage_Catalog_Block_Product_List {

    public function addFilter($code, $value) {
        $this->_getProductCollection()->addAttributeToSelect($code);
        $this->_getProductCollection()->addAttributeToFilter($code, $value);

        return $this;
    }

    public function getcompare() {
        $collection = Mage::getResourceModel('catalog/product_compare_item_collection')
                ->useProductItem(true)
                ->setStoreId(Mage::app()->getStore()->getId());

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $collection->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
        } else {
            $collection->setVisitorId(Mage::getSingleton('log/visitor')->getId());
        }

        Mage::getSingleton('catalog/product_visibility')
                ->addVisibleInSiteFilterToCollection($collection);

        /* Price data is added to consider item stock status using price index */
        $collection->addPriceData();

        $collection->addAttributeToSelect('entity_id')
                ->addUrlRewrite()
                ->load();
        
        return $collection;
    }

}
