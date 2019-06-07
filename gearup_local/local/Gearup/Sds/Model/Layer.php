<?php

class Gearup_Sds_Model_Layer extends Mage_Catalog_Model_Layer
{
    const PRICE_FILTER = 'Price';
    const MANUFACTURER_FILTER = 'Manufacturer';
    const CATEGORY_FILTER = 'Category';

    public function prepareProductCollection($collection)
    {
        $_category = Mage::getModel('catalog/layer')->getCurrentCategory();

        $collection
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite($this->getCurrentCategory()->getId());
        if ($_category->getCategoryDeal() || Mage::app()->getRequest()->getParam('sds')) {
            if (Mage::helper('catalog/category_flat')->isEnabled()) {
                $collection->getSelect()->joinInner(array('sds'=> Mage::getConfig()->getTablePrefix().'catalog_product_flat_'.Mage::app()->getStore()->getStoreId()), "e.entity_id = sds.entity_id AND sds.same_day_shipping = '1'",array('sds.same_day_shipping'));
            } else {
                $collection->addAttributeToFilter('same_day_shipping', array('eq' => 1));
            }
        }

        if(!$_category->getCategoryDeal() && $_category->getShowCategoryFilter()){

            $collection->joinField(
                    'category_id', 'catalog/category_product', 'category_id', 
                    'product_id = entity_id', null, 'left'
                )
                ->addAttributeToFilter('category_id', array(
                        array('in' => array($_category->getId(), Mage::app()->getRequest()->getParam('cat'))),
                ));            
        }

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

        return $this;
    }
}
