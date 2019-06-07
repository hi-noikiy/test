<?php

class MageWorkshop_DetailedReview_Model_Review_Indexer extends Mage_Index_Model_Indexer_Abstract
{
    
    /**
     * Retrieve Indexer name
     * @return string
     */
    public function getName()
    {
        return 'MageWorkshop: Verified Buyer Indexer';
    }
    
    /**
     * Retrieve Indexer description
     * @return string
     */
    public function getDescription()
    {
        return 'Reindex verified buyer for Review Entity';
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
     * @return MageWorkshop_DetailedReview_Model_Mysql4_Purchase
     */
    protected function _getIndexer()
    {
        return Mage::getResourceModel('detailedreview/purchase');
    }
    
    /**
     * Rebuild all index data
     */
    public function reindexAll()
    {
        try {
            $this->_getIndexer()->updateData();
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
    }
}