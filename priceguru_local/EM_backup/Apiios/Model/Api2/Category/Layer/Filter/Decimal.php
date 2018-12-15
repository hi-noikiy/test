<?php
class EM_Apiios_Model_Api2_Category_Layer_Filter_Decimal extends Mage_Catalog_Model_Layer_Filter_Decimal
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
     * Prepare text of item label
     *
     * @param   int $range
     * @param   float $value
     * @return  string
     */
    protected function _renderItemLabel($range, $value)
    {
        $from   = $this->getStore()->formatPrice(($value - 1) * $range, false);
        $to     = $this->getStore()->formatPrice($value * $range, false);
        return Mage::helper('catalog')->__('%s - %s', $from, $to);
    }
    
    /**
     * Apply decimal range filter to product collection
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return EM_Apiios_Model_Api2_Category_Layer_Filter_Decimal
     */
    public function applyios(Zend_Controller_Request_Abstract $request)
    {
        /**
         * Filter must be string: $index, $range
         */
        $filter = $request->getParam($this->getRequestVar());
        if (!$filter) {
            return $this;
        }

        $filter = explode(',', $filter);
        if (count($filter) != 2) {
            return $this;
        }

        list($index, $range) = $filter;
        if ((int)$index && (int)$range) {
            $this->setRange((int)$range);

            $this->_getResource()->applyFilterToCollection($this, $range, $index);
            $this->getLayer()->getState()->addFilter(
                $this->_createItem($this->_renderItemLabel($range, $index), $filter)
            );

            $this->_items = array();
        }

        return $this;
    }
}
?>
