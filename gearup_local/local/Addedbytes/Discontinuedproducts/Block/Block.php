<?php

class Addedbytes_Discontinuedproducts_Block_Block extends Mage_Catalog_Block_Product_Abstract
{

    protected $_items;

    protected $_itemCollection;

    protected $_itemLimits = array();

    protected function _prepareData()
    {
        // Load current product
        $product = Mage::registry('product');

        // Set up collection object
        $this->_itemCollection = new Varien_Data_Collection();

        // Get maximum number of items to show
        $limit = Mage::getStoreConfig('discontinuedproducts/display_settings/discontinued_product_count');

        // Get specific alternative products.
        $discontinuedAlternatives = explode(',', $product->getDiscontinuedAlternatives());
        for ($i = 0, $max = count($discontinuedAlternatives); $i < $max; $i++) {
            // Grab product and add to collection
            $altProduct = Mage::getModel('catalog/product')->loadByAttribute('sku', $discontinuedAlternatives[$i]);
            if (($altProduct) && ($altProduct->getDiscontinuedProduct() != 1) && ($altProduct->isSalable())) {
                $this->_itemCollection->addItem($altProduct);
            }

            // Check collection size isn't at maximum
            if ($this->_itemCollection->count() >= $limit) {
                break;
            }
        }

        // Get associated discontinued category.
        $catId = array(3, 755, 178, 177, 796, 1345, 1365, 1368, 1369);
        $discontinuedCategoryId = $product->getDiscontinuedCategory();
        
        if ((is_numeric($discontinuedCategoryId)) && (!in_array($discontinuedCategoryId, $catId)) && ($discontinuedCategoryId > 0)) {
            
            // Load  category and then product collection.
            $discontinuedCategory = Mage::getModel('catalog/category')->load($discontinuedCategoryId);
            $tmpCollection = $discontinuedCategory->getProductCollection()->addAttributeToSelect('*');

            // Only visible items
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($tmpCollection);
            Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($tmpCollection);

            // Don't show discontinued items in this list! Value can be null or no.
            $tmpCollection->addAttributeToFilter(
                array(
                    array('attribute' => 'discontinued_product', 'null' => true),
                    array('attribute' => 'discontinued_product', 'eq' => 'no'),
                ),
                '',
                'left'
            );

            // Limit to number specified
            if ((!$limit) || ($limit < 1)) {
                $limit = 4;
            }
            $tmpCollection->setPageSize($limit);

            $tmpCollection->load();

            foreach ($tmpCollection as $altProduct) {
                // Check collection size isn't at maximum
                if ($this->_itemCollection->count() >= $limit) {
                    break;
                }
                // check for already exist Id
                if(!empty($this->_itemCollection->getAllIds()) && in_array($altProduct->getId(), $this->_itemCollection->getAllIds())) {
                    continue;
                }
                // Add product from category to collection
                $this->_itemCollection->addItem($altProduct);
            }
        }

        // Handle product column count
        $this->_columnCount = Mage::getStoreConfig('discontinuedproducts/display_settings/discontinued_product_column_count');

        return $this;
    }

    protected function _beforeToHtml()
    {
        $this->_prepareData();

        return parent::_beforeToHtml();
    }

    public function getItemCollection()
    {
        return $this->_itemCollection;
    }

    public function getItems()
    {
        if (is_null($this->_items)) {
            $this->_items = $this->getItemCollection()->getItems();
        }

        return $this->_items;
    }

    public function getColumnCount()
    {
        return $this->_columnCount;
    }

    public function getRowCount()
    {
        return ceil(count($this->getItemCollection()->getItems())/$this->getColumnCount());
    }

    public function resetItemsIterator()
    {
        $this->getItems();
        reset($this->_items);
    }

    public function getIterableItem()
    {
        $item = current($this->_items);
        next($this->_items);

        return $item;
    }
}
