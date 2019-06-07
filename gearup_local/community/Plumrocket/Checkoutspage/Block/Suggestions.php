<?php
/**
 * Plumrocket Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End-user License Agreement
 * that is available through the world-wide-web at this URL:
 * http://wiki.plumrocket.net/wiki/EULA
 * If you are unable to obtain it through the world-wide-web, please
 * send an email to support@plumrocket.com so we can send you a copy immediately.
 *
 * @package     Plumrocket_Checkoutspage
 * @copyright   Copyright (c) 2015 Plumrocket Inc. (http://www.plumrocket.com)
 * @license     http://wiki.plumrocket.net/wiki/EULA  End-user License Agreement
 */


class Plumrocket_Checkoutspage_Block_Suggestions extends Mage_Catalog_Block_Product_Abstract
{

    protected $_mapRenderer = 'msrp_item';
    protected $_itemCollection;

    protected $_linkTypes = 'Related';


    public function getLinksType()
    {
        if ($this->getIsEmail()) {
            return Mage::getStoreConfig('checkoutspage/order_email/suggestions_type');
        }
        return $this->_getSettings('type');
    }

    public function getOrder()
    {
        if (!$this->hasData('order')) {
            $this->setData('order',
                $this->helper('checkoutspage')->getOrder()
            );
        }
        return $this->getData('order');
    }


    public function getItems()
    {
        if (!$this->getOrder() || !$this->isEnabled()) {
            return array();
        }

        if (Mage::getSingleton('plumbase/observer')->customer() == Mage::getSingleton('plumbase/product')->currentCustomer()) {
            if (is_null($this->_itemCollection)) {

                if ($this->showRecentlyViewedProducts()) {
                    $this->_itemCollection = $this->getRecentlyViewedItems();
                } else {
                    $this->_itemCollection = array();

                    $productIds = $this->_getAllProductIds($this->getOrder());
                    $collection = $this->_getCollection()
                        ->addProductFilter($productIds);

                    $collection->getSelect()
                        ->where('e.entity_id NOT IN (?)', $productIds)
                        ->group('e.entity_id');

                    foreach ($collection as $product) {
                        $product->setDoNotUseCategoryId(true);
                        $this->_itemCollection[] = $product;
                    }

                    $this->_randomize();
                }

                
            }
            return $this->_itemCollection;
        }
    }
    public function getRelatedItems(){
        $id = $this->_getAllProductIds($this->getOrder());

        $currentProductId =  $id[0];
        $product = Mage::getModel('catalog/product')->load($currentProductId);
        $categoryId = $product->getCategoryIds();

        $productCollection = Mage::getModel('catalog/product')->getCollection()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', array('neq' => $product->getId()))
                    ->addCategoryFilter(Mage::getModel('catalog/category')->load($categoryId[0]));
                    Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($productCollection);
                        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($productCollection);
                        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($productCollection); 
                        $productCollection->getSelect()->order('RAND()');
                        $productCollection->getSelect()->limit(8);

        return $productCollection;		
       
    }

    public function getItemsCount()
    {
        return $this->_getSettings('number');
    }


    public function isEnabled()
    {
        return $this->_getSettings('enabled');
    }


    protected function _getAllProductIds($order)
    {
        $ids = array();
        foreach ($order->getAllItems() as $_item) {
            $ids[] = $_item->getProductId();
        }

        return $ids;
    }


    protected function _getCollection()
    {
        $type = $this->getLinksType() ? $this->getLinksType() : $this->_linkTypes;

        $method = 'use'.ucfirst($type).'Links';

        $collection = Mage::getModel('catalog/product_link')->$method()
            ->getProductCollection()
            ->setStoreId(Mage::app()->getStore()->getId())
            ->addStoreFilter();
        $this->_addProductAttributesAndPrices($collection);

        Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($collection);

        return $collection;
    }


    protected function _randomize()
    {
        shuffle($this->_itemCollection);
        if (count($this->_itemCollection) > $this->getItemsCount()) {
            array_splice($this->_itemCollection, $this->getItemsCount());
        }

        return $this;
    }


    protected function _getSettings($fiels)
    {
        return Mage::getStoreConfig('checkoutspage/suggestions/'.$fiels);
    }


    public function getAddToCompareUrl($product)
    {
        return Mage::getUrl('catalog/product_compare/add', array(
            'product' => $product->getId(),
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => urlencode(base64_encode('homepage')),
            Mage_Core_Model_Url::FORM_KEY => $this->_getSingletonModel('core/session')->getFormKey()
        ));
    }


    public function showRecentlyViewedProducts()
    {
        if ($this->getIsEmail()) {
            return false;
        }

        return ( $this->_getSettings('type') == Plumrocket_Checkoutspage_Model_System_Config_ProductTypes::PRODUCT_RECENTLY_VIEWED);
    }


    public function getCustomerId()
    {
        return $this->getOrder()->getCustomerId();
    }


    public function getRecentlyViewedItems()
    {
        $attributes = Mage::getSingleton('catalog/config')->getProductAttributes();

        $model = Mage::getModel('reports/product_index_viewed');

        $collection = $model->getCollection()
            ->addAttributeToSelect($attributes);

            if ($this->getCustomerId()) {
                $collection->setCustomerId($this->getCustomerId());
            }

            $collection->excludeProductIds($model->getExcludeProductIds())
                ->addUrlRewrite()
                ->setPageSize($this->getItemsCount())
                ->setCurPage(1);

        /* Price data is added to consider item stock status using price index */
        $collection->addPriceData();

        $ids = $this->getProductIds();
        if (empty($ids)) {
            $collection->addIndexFilter();
        } else {
            $collection->addFilterByIds($ids);
        }
        $collection->setAddedAtOrder();
        if ($this->_useProductIdsOrder && is_array($ids)) {
            $collection->setSortIds($ids);
        }

        Mage::getSingleton('catalog/product_visibility')
            ->addVisibleInSiteFilterToCollection($collection);

        return $collection;

    }

}