<?php

/**
  pradeep.kumarrcs67@gmail.com

 */
class Sugarcode_Bestsellersort_Model_Indexer extends Mage_Index_Model_Indexer_Abstract {

    protected $_matchedEntities = array(
        'sugarcode_mostviewedsellersort' => array(
            Mage_Index_Model_Event::TYPE_SAVE
        )
    );
    // var to protect multiple runs
    protected $_registered = false;
    protected $_processed = false;
    protected $_categoryId = 0;
    protected $_productIds = array();

    /**
     * not sure why this is required.
     * _registerEvent is only called if this function is included.
     *
     * @param Mage_Index_Model_Event $event
     * @return bool
     */
    public function matchEvent(Mage_Index_Model_Event $event) {
        return;
        //return Mage::getModel('catalog/category_indexer_product')->matchEvent($event);
    }

    public function getName() {
        return Mage::helper('core')->__('Most Viewed Indexer');
    }

    public function getDescription() {
        return Mage::helper('core')->__('To count most viewed products');
    }

    protected function _registerEvent(Mage_Index_Model_Event $event) {
        return $this;
    }

    /**
     * Register event data during product save process
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerProductEvent(Mage_Index_Model_Event $event) {
        $eventType = $event->getType();

        if ($eventType == Mage_Index_Model_Event::TYPE_SAVE || $eventType == Mage_Index_Model_Event::TYPE_MASS_ACTION) {
            $process = $event->getProcess();
            $this->_productIds = $event->getDataObject()->getData('product_ids');
            $this->flagIndexRequired($this->_productIds, 'products');
            $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }
    }

    protected function _processEvent(Mage_Index_Model_Event $event) {
        // process index event
        if (!$this->_processed) {
            $this->_processed = true;
        }
    }

    public function flagIndexRequired($ids = array(), $type = 'products') {
        $this->reindexAll();
    }

    public function reindexAll() {

        // reindex all data which are flagged 1 | initFilteredProductsCount
        $collection = Mage::getModel('catalog/product')->getCollection();
        $collection->getSelect()
                ->joinLeft(array("t1" => 'report_event'), "e.entity_id = t1.object_id", array("views" => "COUNT(t1.object_id)"))
                ->group('e.entity_id')->order('views DESC');

        $this->_writeAdaptor = Mage::getSingleton('core/resource')->getConnection('core_write');
        $this->_writeAdaptor->query('TRUNCATE TABLE ln_mostviewed');
        
        foreach ($collection as $v) {
            try {
                $this->_writeAdaptor->insert(
                        "ln_mostviewed", array("entity_id" => $v->getData("entity_id"), "view_count" => $v->getData("views"))
                );
            } catch (Exception $e) {
                Mage::log($e->getMessage());
                return;
            }
        }
    }

}
