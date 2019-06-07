<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_fpc
 * @version   1.0.87
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */


if (Mage::helper('mstcore')->isModuleInstalled('Mirasvit_AsyncIndex') && class_exists('Mirasvit_AsyncIndex_Model_Process')) {
    abstract class Mirasvit_Fpc_Model_Index_Process_Abstract extends Mirasvit_AsyncIndex_Model_Process {

    }
} else {
    abstract class Mirasvit_Fpc_Model_Index_Process_Abstract extends Mage_Index_Model_Process {

    }
}

class Mirasvit_Fpc_Model_Index_Process extends Mirasvit_Fpc_Model_Index_Process_Abstract
{

    protected $_cleanTags = array();

    /**
     * Process event with assigned indexer object
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_Index_Model_Process
     */
    public function processEvent(Mage_Index_Model_Event $event)
    {
        if (Mage::getSingleton('fpc/config')->getUpdateStockMethod() != Mirasvit_Fpc_Model_Config::UPDATE_STOCK_METHOD_REINDEX) {
            return parent::processEvent($event);
        }

        if (!$this->matchEvent($event)) {
            return $this;
        }
        if ($this->getMode() == self::MODE_MANUAL) {
            $this->changeStatus(self::STATUS_REQUIRE_REINDEX);
            return $this;
        }

        $this->_clearCacheByTags($event, $force = false);

        $this->_getResource()->updateProcessStartDate($this);
        $this->_setEventNamespace($event);
        $isError = false;

        try {
            $this->getIndexer()->processEvent($event);
        } catch (Exception $e) {
            $isError = true;
        }
        $event->resetData();
        $this->_resetEventNamespace($event);
        $this->_getResource()->updateProcessEndDate($this);
        $event->addProcessId($this->getId(), $isError ? self::EVENT_STATUS_ERROR : self::EVENT_STATUS_DONE);

        $this->_clearCacheByTags(null, true);

        return $this;
    }

    /**
     * Очищаем кеш исходя из события
     * @param  object  $event
     * @param  boolean $force
     * @return object
     */
    protected function _clearCacheByTags($event, $force = false)
    {
        if ($event != null) {
            $cacheTag = $event->getData('entity') . '_' . $event->getData('entity_pk');
            $this->_cleanTags[] = $cacheTag;
        }

        if ($force && count($this->_cleanTags)) {
            foreach ($this->_cleanTags as $idx => $tag) {
                $this->_cleanTags[$idx] = strtoupper($tag);
            }

            Mage::getSingleton('fpc/cache')->clearCacheByTags($this->_cleanTags);

        }

        return $this;
    }

    /**
     * Reindex all data what this process responsible is
     *
     */
    public function reindexAll($force = false)
    {
        if (Mage::helper('mstcore')->isModuleInstalled('Mirasvit_AsyncIndex')) {
            return parent::reindexAll($force);
        } elseif (Mage::getSingleton('fpc/config')->getUpdateStockMethod() != Mirasvit_Fpc_Model_Config::UPDATE_STOCK_METHOD_FULL_REINDEX
            || Mage::getVersion() < '1.7.0.0') {
            return parent::reindexAll();
        }

        if ($this->isLocked()) {
            Mage::throwException(Mage::helper('index')->__('%s Index process is working now. Please try run this process later.', $this->getIndexer()->getName()));
        }

        $processStatus = $this->getStatus();

        $this->_getResource()->startProcess($this);
        $this->lock();
        try {
            $eventsCollection = $this->getUnprocessedEventsCollection();

            /** @var $eventResource Mage_Index_Model_Resource_Event */
            $eventResource = Mage::getResourceSingleton('index/event');

            if ($this->getIndexerCode() == 'cataloginventory_stock') {
                $eventsData = $eventsCollection->getData();
            }

            if ($eventsCollection->count() > 0 && $processStatus == self::STATUS_PENDING
                || $this->getForcePartialReindex()
            ) {
                $this->_getResource()->beginTransaction();
                try {
                    $this->_processEventsCollection($eventsCollection, false);
                    $this->_getResource()->commit();
                } catch (Exception $e) {
                    $this->_getResource()->rollBack();
                    throw $e;
                }
            } else {
                //Update existing events since we'll do reindexAll
                $eventResource->updateProcessEvents($this);
                $this->getIndexer()->reindexAll();
            }
            $this->unlock();

            $unprocessedEvents = $eventResource->getUnprocessedEvents($this);
            if ($this->getMode() == self::MODE_MANUAL && (count($unprocessedEvents) > 0)) {
                $this->_getResource()->updateStatus($this, self::STATUS_REQUIRE_REINDEX);
            } else {
                $this->_getResource()->endProcess($this);
            }
        } catch (Exception $e) {
            $this->unlock();
            $this->_getResource()->failProcess($this);
            throw $e;
        }

        if ($this->getIndexerCode() == 'cataloginventory_stock'
            && is_array($eventsData) && count($eventsData)) {
                $this->_clearCacheByTagsFullReindex($eventsData);
        }
        Mage::dispatchEvent('after_reindex_process_' . $this->getIndexerCode());
        return $this;
    }


     /**
     * Очищаем кеш после cataloginventory_stock reindex
     * @param  object  $event
     * @param  boolean $force
     * @return object
     */
    protected function _clearCacheByTagsFullReindex($eventData)
    {
        $cleanTags = array();

        foreach ($eventData as $event) {
            if (strtoupper($event['entity'] . '_') == Mirasvit_Fpc_Model_Config::CATALOG_PRODUCT_TAG) {
                $tag = Mirasvit_Fpc_Model_Config::CATALOG_PRODUCT_TAG . $event['entity_pk'];
                $cleanTags[] = strtoupper($tag);
            }
        }

        if ($cleanTags) {
            Mage::getSingleton('fpc/cache')->clearCacheByTags($cleanTags);
        }

        return $this;
    }
}