<?php

class MageWorkshop_DetailedReview_Model_Mysql4_Product_Indexer extends Mage_Core_Model_Resource_Abstract
{
    /**
     * @var Mage_Core_Model_Resource $_resource
     */
    protected $_resource;
    
    /**
     * @var Varien_Db_Adapter_Interface $_read
     */
    protected $_read;
    
    /**
     * @var Varien_Db_Adapter_Interface $_write
     */
    protected $_write;
    
    /**
     * @var Varien_Db_Select $_bestsellingSelect
     */
    protected $_bestsellingSelect;
    
    /**
     * @var Varien_Db_Select $_mostReviewedSelect
     */
    protected $_mostReviewedSelect;
    
    /**
     * @var Varien_Db_Select $_highlyRatedSelect
     */
    protected $_highlyRatedSelect;
    
    /**
     * Resource initializations
     */
    public function _construct()
    {
       $this->_resource = Mage::getSingleton('core/resource');
    }
    
    /**
     * Refresh All Indexes
     * @throws Exception
     */
    public function refreshAllIndex()
    {
       try {
           $this->reindexBestselling();
       } catch (Exception $e) {
           Mage::log('MageWorkshop: failed bestselling Reindex');
           throw  $e;
       }
    
        try {
            $this->reindexMostReviewed();
        } catch (Exception $e) {
            Mage::log('MageWorkshop: failed most reviewed Reindex');
            throw  $e;
        }
    
        try {
            $this->reindexHighlyRated();
        } catch (Exception $e) {
            Mage::log('MageWorkshop: failed highly rated Reindex');
            throw  $e;
        }
    }
   
    /**
     * @param null|int|array $id
     * @throws Exception
     */
    public function reindexBestselling($id = null)
    {
        try {
            $attributeCode = 'popularity_by_sells';
            $this->_prepareBestsellingData();
            $this->_addProductEntityFilter($id, $this->_bestsellingSelect);
            $this->_process($attributeCode, $this->_bestsellingSelect);
            $this->_updateFlatProductTable($attributeCode);
        } catch (Exception $e) {
            throw  $e;
        }
    }
    
    /**
     * @param null|int|array $id
     * @throws Exception
     */
    public function reindexMostReviewed($id = null)
    {
        try {
            $attributeCode = 'popularity_by_reviews';
            $this->_prepareMostReviewedData();
            $this->_addProductEntityFilter($id, $this->_mostReviewedSelect);
            $this->_process($attributeCode, $this->_mostReviewedSelect);
            $this->_updateFlatProductTable($attributeCode);
        } catch (Exception $e) {
            throw  $e;
        }
    }
    
    /**
     * @param null|int|array $id
     * @throws Exception
     */
    public function reindexHighlyRated($id = null)
    {
        try {
            $attributeCode = 'popularity_by_rating';
            $this->_prepareHighlyRatedData();
            $this->_addProductEntityFilter($id, $this->_highlyRatedSelect);
            $this->_process($attributeCode, $this->_highlyRatedSelect);
            $this->_updateFlatProductTable($attributeCode);
        } catch (Exception $e) {
            throw  $e;
        }
    }
    
    
   protected function _prepareBestsellingData()
   {
       /** @var Varien_Db_Select $select */
       $this->_bestsellingSelect = $this->_getReadAdapter()->select()
           ->from(
               array('so' => $this->getTable('sales/order')),
               array('p.entity_type_id', 'eav.attribute_id', 'so.store_id', 'p.entity_id', 'COUNT(*) AS value')
           )
           ->join(
               array('soi' => $this->getTable('sales/order_item')),
               'so.entity_id = soi.order_id',
               null
           )
           ->join( array('p' => $this->getTable('catalog/product')),
               'p.entity_id = soi.product_id',
              null
           )
           ->join(
               array('eav' => $this->getTable('eav/attribute')),
               'eav.entity_type_id = p.entity_type_id',
               null
           )
           ->where('so.status = ?', Mage_Sales_Model_Order::STATE_COMPLETE )
           ->where('so.store_id > ?', 0)
           ->where('eav.attribute_code = ?', 'popularity_by_sells')
           ->group(array('p.entity_id', 'so.store_id'));
   }
    

   protected function _prepareMostReviewedData()
   {
       /** @var Varien_Db_Select $select */
       $this->_mostReviewedSelect = $this->_getReadAdapter()->select()
           ->from(
               array('r' => $this->getTable('review/review')),
               array('p.entity_type_id', 'eav.attribute_id', 'rs.store_id', 'p.entity_id', 'COUNT(*) AS value')
           )
           ->join(
               array('rs' => $this->getTable('review/review_store')),
               'r.review_id = rs.review_id',
               null
           )
           ->join(
               array('re' => $this->getTable('review/review_entity')),
               'r.entity_id = re.entity_id',
               null
           )
           ->join( array('p' => $this->getTable('catalog/product')),
               'r.entity_pk_value = p.entity_id',
               null
           )
           ->join(
               array('eav' => $this->getTable('eav/attribute')),
               'eav.entity_type_id = p.entity_type_id',
               null
           )
           ->where('re.entity_code = ?', Mage_Review_Model_Review::ENTITY_PRODUCT_CODE)
           ->where('r.status_id = ?', Mage_Review_Model_Review::STATUS_APPROVED)
           ->where('rs.store_id > ?', 0)
           ->where('eav.attribute_code = ?', 'popularity_by_reviews')
           ->group(array('p.entity_id', 'rs.store_id'));
   }
    

   protected function _prepareHighlyRatedData()
   {
       /** @var Varien_Db_Select $select */
       $this->_highlyRatedSelect = $this->_getReadAdapter()->select()
           ->from(
               array('rating_aggregated' => $this->getTable('rating/rating_vote_aggregated')),
               array('p.entity_type_id', 'eav.attribute_id', 'store_id', 'p.entity_id', 'ROUND (AVG(rating_aggregated.percent_approved), 0) AS value')
           )
           ->join(
               array('rating' => $this->getTable('rating/rating')),
               'rating_aggregated.rating_id = rating.rating_id',
               null
           )
           ->join(
               array('rating_entity' => $this->getTable('rating/rating_entity')),
               'rating.entity_id = rating_entity.entity_id',
               null
           )
           ->join( array('p' => $this->getTable('catalog/product')),
               'rating_aggregated.entity_pk_value = p.entity_id',
               null
           )
           ->join(
               array('eav' => $this->getTable('eav/attribute')),
               'eav.entity_type_id = p.entity_type_id',
               null
           )
           ->where('rating_entity.entity_code = ?', Mage_Rating_Model_Rating::ENTITY_PRODUCT_CODE)
           ->where('rating_aggregated.store_id > ?', 0)
           ->where('eav.attribute_code = ?', 'popularity_by_rating')
           ->group(array('p.entity_id', 'rating_aggregated.store_id'));
   }
    
    /**
     * @param array|int $id
     * @param Varien_Db_Select $select
     */
   protected function _addProductEntityFilter($id, $select)
   {
       if ($id && $select) {
           if (is_array($id)) {
               $select->where('p.entity_id IN (?)', $id);
           } else {
               $select->where('p.entity_id = ?', $id);
           }
       }
   }
   
   protected function _process($attributeCode, $select)
   {
       $write  = $this->_getWriteAdapter();
       $this->beginTransaction();
       try {
           $attribute = Mage::getResourceModel('catalog/eav_attribute')
               ->loadByCode(
                   Mage_Catalog_Model_Product::ENTITY,
                   $attributeCode
               );
           
           $insertSelect = $write->insertFromSelect(
               $select,
               $attribute->getBackendTable(),
               array('entity_type_id', 'attribute_id', 'store_id', 'entity_id', 'value'),
               Varien_Db_Adapter_Interface::INSERT_ON_DUPLICATE
           );
           
           $write->query($insertSelect);
           $this->commit();
       } catch (Exception $e) {
           $this->rollBack();
           
           Mage::getSingleton('index/indexer')
               ->getProcessByCode(MageWorkshop_DetailedReview_Helper_Config::DETAILEDREVIEW_PRODUCT_ATTR_INDEXER_CODE)
               ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
           
           Mage::logException($e);
           throw $e;
       }
   }
    
    /**
     * Get resource table name, validated by db adapter
     *
     * @param   string|array $modelEntity
     * @return  string
     */
   protected function getTable($modelEntity)
   {
       return $this->_resource->getTableName($modelEntity);
   }
    
    /**
     * Retrieve connection for read data
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getReadAdapter()
    {
        if (!$this->_read) {
            $this->_read =  $this->_resource->getConnection('read');
        }
        return $this->_read;
    }
    
    /**
     * Retrieve connection for write data
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getWriteAdapter()
    {
        if (!$this->_write) {
            $this->_write = $this->_resource->getConnection('write');
        }
        return $this->_write;
    }
    
    /**
     * @param string $attributeCode
     * @return $this
     */
    protected function _updateFlatProductTable($attributeCode)
    {
        if (Mage::getStoreConfig('catalog/frontend/flat_catalog_product')) {
            $indexer = Mage::getResourceModel('catalog/product_flat_indexer');
            $attribute = $indexer->getAttribute($attributeCode);
            /** @var Mage_Core_Model_Store $store */
            foreach (Mage::app()->getStores() as $store) {
                $indexer->updateAttribute($attribute, $store->getId());
            }
        }
        
        return $this;
    }
}