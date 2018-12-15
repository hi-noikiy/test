<?php
class EM_Apiios_Model_Api2_Category_Layer_Filter_Attribute extends Mage_Catalog_Model_Layer_Filter_Attribute
{
    protected $_store;

    public function setStore($store){
        $this->_store = $store;
        return $this;
    }

    public function getStore(){
        return $this->_store;
    }

    /**
     * Retrieve current store id scope
     *
     * @return int
     */
    public function getStoreId()
    {
        $storeId = $this->_getData('store_id');
        if (is_null($storeId)) {
            $storeId = $this->getStore()->getId();
        }
        return $storeId;
    }

    /**
     * Retrieve Website ID scope
     *
     * @return int
     */
    public function getWebsiteId()
    {
        $websiteId = $this->_getData('website_id');
        if (is_null($websiteId)) {
            $websiteId = $this->getStore()->getWebsiteId();
        }
        return $websiteId;
    }
    
    /**
     * Apply attribute option filter to product collection
     *
     * @param   Zend_Controller_Request_Abstract $request
     * @return  EM_Apiios_Model_Api2_Category_Layer_Filter_Attribute
     */
    public function applyios(Zend_Controller_Request_Abstract $request)
    {
        $filter = $request->getParam($this->_requestVar);
        if (is_array($filter)) {
            return $this;
        }
        $text = $this->_getOptionText($filter);
        if ($filter && strlen($text)) {
            $this->_getResource()->applyFilterToCollection($this, $filter);
            $this->getLayer()->getState()->addFilter($this->_createItem($text, $filter));
            $this->_items = array();
        }
        return $this;
    }
}
?>
