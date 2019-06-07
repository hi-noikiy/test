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


class Plumrocket_Checkoutspage_Block_Bestseller extends Mage_Catalog_Block_Product_Abstract
{

    protected $_mapRenderer = 'msrp_item';
    protected $_itemCollection;

    protected function getCollection()
    {
        $storeId = (int) Mage::app()->getStore()->getId();
 
        // Date
        $date = new Zend_Date();
        $toDate = $date->setDay(1)->getDate()->get('Y-MM-dd');
        $fromDate = $date->subMonth(1)->getDate()->get('Y-MM-dd');
 
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addStoreFilter()
            ->addPriceData()
            ->addTaxPercents()
            ->addUrlRewrite();
            
        $collection->getSelect()
            ->joinLeft(
                array('aggregation' => $collection->getResource()->getTable('sales/bestsellers_aggregated_monthly')),
                "e.entity_id = aggregation.product_id AND aggregation.store_id={$storeId} AND aggregation.period BETWEEN '{$fromDate}' AND '{$toDate}'",
                array('SUM(aggregation.qty_ordered) AS sold_quantity')
            )
            ->group('e.entity_id')
            ->order(array('sold_quantity DESC', 'e.created_at'));
 
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        $collection->setPageSize(8);

        return $collection;
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

    public function getBestsellerCollection()
    {
        $product = Mage::registry('product');
        $storeId = Mage::app()->getStore()->getId();
        $arrayCatIds = array(); 

        if($product)    
        {
            $catIds = $product->getCategoryId();
            $arrayCatIds = explode(",", $catIds);

            $products = Mage::getResourceModel('reports/product_collection')
            ->addAttributeToSelect('*')
            ->addUrlRewrite()
            ->addOrderedQty()
            ->addMinimalPrice()
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
            ->setOrder('ordered_qty', 'desc');

            /*$products->joinField('category_id',
                'catalog/category_product',
                'category_id',
                'product_id=entity_id',
                null,
                'left');
            $products->addAttributeToFilter('category_id', array('in' => $arrayCatIds));*/
        }else {
            $products = Mage::getResourceModel('reports/product_collection')
            ->addAttributeToSelect('*')
            ->addUrlRewrite()
            ->addOrderedQty()
            ->addMinimalPrice()
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
            ->setOrder('ordered_qty', 'desc');  
        }

        $productFlatData = Mage::getStoreConfig('catalog/frontend/flat_catalog_product');
        if($productFlatData == "1")
        {
            $products->getSelect()->joinLeft(
                array('flat' => 'catalog_product_flat_'.$storeId),
                "(e.entity_id = flat.entity_id)",                    
                array(
                   'flat.name AS name','flat.thumbnail AS thumbnail','flat.price AS price','flat.special_price AS special_price','flat.special_from_date AS special_from_date','flat.special_to_date AS special_to_date'
                )
            );
        }

        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);

        if(!empty($arrayCatIds)){
            $category = Mage::getModel('catalog/category')->load($arrayCatIds[0]);
            $products->addCategoryFilter($category);
        }
        $products->getSelect()->limit(8);

        return $products;
    }

}