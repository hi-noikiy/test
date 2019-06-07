<?php

class MageWorkshop_DetailedReview_Model_Product_Indexer extends Mage_Index_Model_Indexer_Abstract
{
    /**
     * Retrieve Indexer name
     * @return string
     */
    public function getName()
    {
        return 'MageWorkshop: Review Indexer';
    }
    
    /**
     * Retrieve Indexer description
     * @return string
     */
    public function getDescription()
    {
        return "Reindex 'Bestselling', 'Most Reviewed', 'Highly Rated' Product attributes";
    }
    
    /**
     * Register data required by process in event object
     * @param Mage_Index_Model_Event $event
     * @return $this
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        return $this;
    }
    
    /**
     * Process event
     * @param Mage_Index_Model_Event $event
     * @return $this
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        return $this;
    }
    
    /**
     * @return MageWorkshop_DetailedReview_Model_Mysql4_Product_Indexer
     */
    protected function _getIndexer()
    {
        return Mage::getResourceSingleton('detailedreview/product_indexer');
    }
    
    /**
     * Rebuild all index data
     */
    public function reindexAll()
    {
        try {
            $this->_getIndexer()->refreshAllIndex();
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
    }
}