<?php

/**
 * Items list block
 * 
 * @method void setCode(string $code)
 * @method string getCode()
 */
class Hatimeria_AttributeBrowser_Block_Item_List extends Mage_Core_Block_Template
{
    protected $_attribute;
    
    protected function _construct()
    {
        parent::_construct();
        $this->addData(array(
            'cache_lifetime' => 12*3600,
            'cache_tags' => array(Mage_Core_Block_Template::CACHE_GROUP)
        ));
    }
    
    /**
     * Current attribute
     * 
     * @return array
     */
    protected function _getAttribute()
    {
        if (!isset($this->_attribute))
        {
            $model = Mage::getSingleton('attributebrowser/list');
            
            if ($this->getCode())
            {
                $this->_attribute = $model->getAttribute($this->getCode());
            }
            elseif ($model->hasAttributeContext())
            {
                $this->_attribute = $model->getCurrentAttribute();
            }
            else {
                Mage::throwException('Must specify code by setCode method or use attribute context');
            }
        }
        
        return $this->_attribute;
    }
    
    /**
     * Items
     * 
     * @return array
     */
    public function getItems()
    {
        $attr = $this->_getAttribute();

        return $attr['items'];
    }

    /**
     * Elements available on front:
     *
     * @return array
     */
    public function getAvailableItems()
    {
        $items = array();
        $ids = explode(',', Mage::getStoreConfig('catalog/manufacturer/avoided_items'));

        foreach ($this->getItems() as $key => $item) {
            if (!in_array($item['id'], $ids)) {
                $items[$key] = $item;
            }
        }

        return $items;
    }
    
    /**
     * Attribute title
     * 
     * @return string
     */
    public function getTitle()
    {
        $attr = $this->_getAttribute();
        
        return $attr['title'];
    }
    
    /**
     * Route
     * 
     * @return string
     */
    public function getRoute()
    {
        $attr = $this->_getAttribute();
        
        return $attr['route'];
    }
    
    public function _getProducts($item)
    {
        $collection = Mage::getModel('catalog/product')->getCollection();
        Mage::getModel('cataloginventory/stock')->addInStockFilterToCollection($collection);
        Mage::getModel('catalog/layer')->prepareProductCollection($collection);

        $attr = $this->_getAttribute();
        $collection->addAttributeToFilter($attr['code'], $item['id']);
        $collection->getSelect()->limit(1);
        return $collection->getSize();
    }
}