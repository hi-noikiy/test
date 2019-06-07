<?php

/**
 * AttributeBrowsers Model
 */
class Hatimeria_AttributeBrowser_Model_List 
{
    /**
     * Attributes
     * 
     * @var type 
     */
    protected $_attributes = array();
    
    /**
     * Current code
     * 
     * @var string
     */
    protected $_currentKey;
    
    /**
     *
     * @var type 
     */
    protected $_currentCode;
    
    /**
     * Loads attribute with options
     * 
     * @param string $code
     */
    protected function loadAttribute($code)
    {
        $configModel = Mage::getSingleton('attributebrowser/config');
        $config = $configModel->getConfig();
        
        if (!isset($config[$code]))
        {
            return ;
        }
        
        $attrConfig = $config[$code];
        
        $attributeModel = Mage::getModel('eav/entity_attribute');
        $attributeModel->loadByCode('catalog_product', $code);
        
        $attributeOptionsModel = Mage::getModel('eav/entity_attribute_source_table');
        $attributeOptionsModel->setAttribute($attributeModel);
        $data = $attributeOptionsModel->getAllOptions(false);
        
        $this->_attributes[$code] = array(
            'items' => array(),
            'code' => $code,
            'title' => $attrConfig['title'],
            'route' => $attrConfig['route'],
            'description' => (isset($attrConfig['description']) ? $attrConfig['description'] : ''),
            'keywords' => (isset($attrConfig['keywords']) ? $attrConfig['keywords'] : '')
        );
        
        foreach ($data as $item)
        {
            $key = preg_replace("/[^a-z0-9_]/i", '_', strtolower(trim($item['label'])));
            $this->_attributes[$code]['items'][$key] = array(
                'name' => $item['label'],
                  'id' => $item['value'],
                 'key' => $key
            );
        }
    }
    
    /**
     * Atribute
     * 
     * @param string $code
     * @return array 
     */
    public function getAttribute($code)
    {
        if (!isset($this->_attributes[$code]))
        {
            $this->loadAttribute($code);
        }
        
        return $this->_attributes[$code];
    }
    
    /**
     * One item config 
     * 
     * @param string $code
     * @param string $key
     * @return array|null
     */
    public function getAttributeItem($code, $key)
    {
        $items = $this->getAttributeItems($code);
        
        return isset($items[$key]) ? $items[$key] : null ;
    }
    
    /**
     * One item config 
     * 
     * @param string $code
     * @param string $key
     * @return array|null
     */
    public function getAttributeItems($code)
    {
        $attribute = $this->getAttribute($code);
        
        if (!$attribute)
        {
            return null;
        }
        
        if (isset($attribute['items']))
        {
            return $attribute['items'];
        }
        else
        {
            return null;
        }
    }
    
    /**
     * Set current key 
     * 
     * @param string $key
     * @return Hatimeria_AttributeBrowser_Model_List
     */
    public function setCurrentKey($key)
    {
        $this->_currentKey = $key;
        
        return $this;
    }
    
    /**
     * Get current key
     * 
     * @return string
     */
    public function getCurrentKey()
    {
        return $this->_currentKey;
    }
    
    /**
     * Check id block is in attribute context
     * 
     * @return bool 
     */
    public function hasAttributeContext()
    {
        return isset($this->_currentCode);
    }
    
    /**
     * Check id block is in attribute context
     * 
     * @return bool 
     */
    public function hasAttributeItemContext()
    {
        return isset($this->_currentKey);
    }
    
    /**
     * Set current code
     * 
     * @return Hatimeria_AttributeBrowser_Model_List
     */
    public function setCurrentCode($code)
    {
        $this->_currentCode = $code;
        
        return $this;
    }
    
    /**
     * Get current code
     * 
     * @return string
     */
    public function getCurrentCode()
    {
        return $this->_currentCode;
    }
    
    /**
     * Current Attribute with options
     */
    public function getCurrentAttribute()
    {
        return $this->getAttribute($this->getCurrentCode());
    }
    
    /**
     * Current option 
     */
    public function getCurrentItem()
    {
        return $this->getAttributeItem($this->getCurrentCode(), $this->getCurrentKey());
    }
    
}