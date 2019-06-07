<?php

class Gearup_Mostviewed_Helper_Data extends Amasty_Mostviewed_Helper_Data {

    public function getViewedWith($productId, $block, $exclude = array(), $case = 1) {
        if (is_null($productId) || ($productId === '')) {
            return new Varien_Data_Collection();
        }

        $product = Mage::getModel('catalog/product')->load($productId);

        $size = intVal($this->getBlockConfig($block, 'size'));
        if (!$size) {
            return new Varien_Data_Collection();
        }

        if ($this->getBlockConfig($block, 'data_source') == Amasty_Mostviewed_Model_Source_Datasource::SOURCE_VIEWED) {
            $ids = $this->_getRelatedIdsViewed($product, $block);
        } else {
            $ids = $this->_getRelatedIdsBought($product, $block);
            $ids = array_diff($ids, array($product->getId()));
        }

        if (!count($ids)) {
            return new Varien_Data_Collection();
        }

        $ids = array_diff($ids, $exclude);

        $collection = Mage::getModel('catalog/product')->getResourceCollection();


        $this->_addPricesAndAttributes($collection);
        $this->_addCommonFilters($collection, $block);

        switch ($case):
            case 0:
                $categoryObj = Mage::getModel('catalog/category')->load($product->getCategoryIds()[0]);
                $collection->addCategoryFilter($categoryObj);
                break;
            case 1:
                $this->_addBrandFilter($collection, $product, $block);
                break;
            case 2:
                $quote = Mage::getSingleton('checkout/cart')->getQuote();
                $_msrpPrice = [];
                foreach ($quote->getAllVisibleItems() as $item)                   
                    $_msrpPrice[] = $item->getProduct()->getPrice();                   
                
                $collection->addFieldToFilter('price',['in' => $_msrpPrice] );
        endswitch;


        $this->_prepareSelect($collection, $ids, $size);

        $used = $this->_getUsedIds($collection);

        // append is the last action, because we must display manually added products in any case
        if (Amasty_Mostviewed_Model_Source_Manually::APPEND == $this->getBlockConfig($block, 'manually')) {
            $manuallyIds = $this->_getManuallyAddedIds($block, $productId);
            if (!empty($manuallyIds)) {
                $ids = array();
                $ids = array_merge($manuallyIds, $used);
                // unfortunately we need to load collection again
                $collection = Mage::getModel('catalog/product')->getResourceCollection();
                $collection->addIdFilter($ids);
                $this->_addPricesAndAttributes($collection);
                $this->_prepareSelect($collection, $ids, $size);

                $used = $this->_getUsedIds($collection, $manuallyIds);
            }
        }

        if (!empty($used) && !Mage::registry('ammostviewed_used')) {
            Mage::register('ammostviewed_used', $used, true);
        }

        return $collection;

        /** @todo:
          if the collection is empty, show items from the
          same category or attribute set or with similar price (?)
         */
    }

}
