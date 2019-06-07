<?php

/**
 * AttributeBrowser Config
 */
class Hatimeria_AttributeBrowser_Model_Config 
{
    /**
     * Module Config
     * 
     * @var array
     */
    protected $_config;
    
    /**
     * Routing Map
     * 
     * @var array
     */
    protected $_routingMap;
    
    /**
     * Constructor 
     */
    public function __construct()
    {
        $this->_load();
    }
    
    /**
     * Loads config 
     */
    public function _load()
    {
        $this->_config = Mage::getStoreConfig('attributebrowser');
        
        foreach ($this->_config as $item)
        {
            $route = $item['route'];
            $this->_routingMap[$route] = $item['attribute'];
        }
    }
    
    /**
     * Configuration
     * 
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }
    
    /**
     * Translate route to attribute code
     * 
     * @param string $route
     * @return type 
     */
    public function getAttributeCodeByRoute($route)
    {
        return isset($this->_routingMap[$route]) ? $this->_routingMap[$route] : false ;
    }
}
