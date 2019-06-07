<?php
/**
 * MageWorkshop
 * Copyright (C) 2016 MageWorkshop <info@mage-review.com>
 *
 * @category   MageWorkshop
 * @package    MageWorkshop_DetailedReview
 * @copyright  Copyright (c) 2016 MageWorkshop Co. (https://mage-review.com/)
 * @license    http://opensource.org/licenses/gpl-3.0.html GNU General Public License, version 3 (GPL-3.0)
 * @author     MageWorkshop <info@mage-review.com>
 */
class MageWorkshop_DetailedReview_Model_Sort
{
    /**
     * @return Varien_Db_Adapter_Interface
     */
    public function getConnection()
    {
        return Mage::getSingleton('core/resource')->getConnection('default_setup');
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function refreshAllIndices()
    {
        if ($this->_getProcess()
            && $this->_getProcess()->getMode() === Mage_Index_Model_Process::MODE_MANUAL)
        {
            try {
                $this->changeIndexerStatus(Mage_Index_Model_Process::STATUS_RUNNING);
                Mage::getModel('detailedreview/product_indexer')->reindexAll();
                $this->changeIndexerStatus(Mage_Index_Model_Process::STATUS_PENDING);
            } catch (Exception $e) {
                $this->changeIndexerStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
            }
        }

        return $this;
    }
    
    public function changeIndexerStatus($status)
    {
        $this->_getProcess()->changeStatus($status);
    }
    
    /**
     * @return Mage_Index_Model_Process | false
     */
    protected function _getProcess()
    {
        return Mage::getSingleton('index/indexer')
            ->getProcessByCode(MageWorkshop_DetailedReview_Helper_Config::DETAILEDREVIEW_PRODUCT_ATTR_INDEXER_CODE);
    }

    /**
     * @param Mage_Reports_Model_Mysql4_Product_Sold_Collection $collection
     * @return $this
     */
    protected function addOrdersCountToProductCollection($collection)
    {
        $from = $this->_getFromDate();
        $to = $this->_getToday();

        $orderItemTableName = $collection->getTable('sales/order_item');
        $productFieldName   = 'e.entity_id';

        $collection->getSelect()
            ->joinLeft(
                array('order_items' => $orderItemTableName),
                "order_items.product_id = $productFieldName",
                array()
            )
            ->columns(array('orders' => 'COUNT(order_items2.item_id)'))
            ->group($productFieldName);

        $dateFilter = array('order_items2.item_id = order_items.item_id');
        if ($from && $to) {
            $dateFilter[] = sprintf('(order_items2.created_at BETWEEN "%s" AND "%s")', $from, $to);
        }

        $collection->getSelect()
            ->joinLeft(
                array('order_items2' => $orderItemTableName),
                implode(' AND ', $dateFilter),
                array()
            );
        return $this;
    }


    /**
     * Retrieve start time for report
     * 
     * @return string
     */
    protected function _getFromDate()
    {
        $date = new Zend_Date;
        $date->subDay(10);
        return $date->getIso();
    }
    
    /**
     * Retrieve now
     * 
     * @return string
     */
    protected function _getToday()
    {
        $date = new Zend_Date;
        return $date->getIso();
    } 
}

