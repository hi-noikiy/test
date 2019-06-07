<?php

/**
 * Custom widget
 */
class Mish_Catalog_Block_Product_Widget_Custom extends Mage_Catalog_Block_Product_Abstract implements Mage_Widget_Block_Interface
{
    protected $_productsCount;
    protected $_sku;

    const DEFAULT_PRODUCTS_COUNT = 1;
    const DEFAULT_PRODUCTS_DIRECTION = 'desc';
    const DEFAULT_PRODUCTS_ORDER = 'price';
    
    /**
     * Initialize block's cache
     */
    protected function _construct()
    {
        parent::_construct();

        $this->addData(array(
            'cache_lifetime'    => 86400,
            'cache_tags'        => array(Mage_Catalog_Model_Product::CACHE_TAG),
        ));
    }
    
    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
           'CATALOG_PRODUCT_CUSTOM',
           Mage::app()->getStore()->getId(),
           Mage::getDesign()->getPackageName(),
           Mage::getDesign()->getTheme('template'),
           Mage::getSingleton('customer/session')->getCustomerGroupId(),
           'template' => $this->getTemplate(),
            Mage::app()->getStore()->getCurrentCurrencyCode(),
           $this->getProductsCount(),
           ($this->getRequest()->isSecure() ? 'secured' : 'not-secured')
        );
    }
    
    /**
     * Prepare collection with new products and applied page limits.
     *
     * return Mage_Catalog_Block_Product_New
     */
    protected function _beforeToHtml()
    {
        if (($selectedProduct = $this->getSelectedProduct())) {
            $collection = new Varien_Data_Collection();
            $collection->addItem($selectedProduct);
        }
        else {
            $collection = Mage::getResourceModel('catalog/product_collection');
            $collection->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());

            if ($this->hasFilterCategory()) {
                $category = Mage::getModel('catalog/category')->load($this->getFilterCategory());
                if ($category->getId()) {
                    $collection->addCategoryFilter($category);
                }
            }

            $collection = $this->_addProductAttributesAndPrices($collection)
                ->addStoreFilter()
                ->setOrder($this->getOrder(), $this->getDirection())
                ->setPageSize($this->getProductsCount())
                ->setCurPage(1);
        }
        
        $this->setProductCollection($collection);
        
        return parent::_beforeToHtml();
    }
    
    /**
     * Set how much product should be displayed at once.
     *
     * @param $count
     * @return Mage_Catalog_Block_Product_New
     */
    public function setProductsCount($count)
    {
        $this->_productsCount = $count;
        return $this;
    }

    /**
     * Get how much products should be displayed at once.
     *
     * @return int
     */
    public function getProductsCount()
    {
        if (!isset($this->_productsCount)) {
            $this->_productsCount = self::DEFAULT_PRODUCTS_COUNT;
        }
        return $this->_productsCount;
    }
    
    public function getOrder()
    {
        if (!$this->hasData('order')) {
            return self::DEFAULT_PRODUCTS_ORDER;
        }
        
        return $this->getData('order');
    }
    
    public function getDirection()
    {
        if (!$this->hasData('direction')) {
            return self::DEFAULT_PRODUCTS_DIRECTION;
        }
        
        return $this->getData('direction');
    }
    
    protected function getSelectedProduct()
    {
        /* @var $product Mage_Catalog_Model_Product */
        $model = Mage::getModel('catalog/product');
        $productId = $model->getIdBySku($this->getSku());
        $product = $model->load($productId);
        
        if ($product->getId() && $product->isSalable()) {
            return $product;
        }
        
        return null;
    }
}