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
class MageWorkshop_DetailedReview_Model_Mysql4_Review extends Mage_Review_Model_Mysql4_Review
{
    const MYISAM_ENGINE_NAME = 'MyISAM';

    /**
     * @param Mage_Core_Model_Abstract $review
     * @return $this
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $review)
    {
        /** @var MageWorkshop_DetailedReview_Model_Review $review */
        $reviewDate = null;
        if (!$review->getId()) {
            $date = null;
            if (Mage::app()->getStore()->isAdmin()) {
                $date = Mage::helper('detailedreview')->convertToGMT($review->getCreatedAt());
            }
            $reviewDate = $date ?: Mage::getSingleton('core/date')->gmtDate();
        } else {
            // check if date will change
            if ($review->getData('created_at') && ($review->getData('created_at') != $review->getOrigData('created_at'))) {
                $reviewDate = Mage::helper('detailedreview')->convertToGMT($review->getCreatedAt());
            }
        }

        if ($reviewDate) {
            $review->setCreatedAt($reviewDate);
        }

        if ($review->hasData('stores') && is_array($review->getStores())) {
            $stores = $review->getStores();
            $stores[] = 0;
            $review->setStores($stores);
        } elseif ($review->hasData('stores')) {
            $review->setStores(array($review->getStores(), 0));
        }
        return $this;
    }


    /**
     * @param Mage_Core_Model_Abstract $review
     * @return $this
     */
    protected function _afterSave(Mage_Core_Model_Abstract $review)
    {
        /** @var MageWorkshop_DetailedReview_Model_Review $review */
        if (!Mage::getStoreConfig('detailedreview/settings/enable')) {
            return parent::_afterSave($review);
        }

        $image = $review->getImage();
        $request = Mage::app()->getRequest();
        if ($review->getPros() && (is_null($request->getParam('pros')) && is_null($request->getParam('user_pros')))) {
            $review->setPros(null);
        }
        if ($review->getCons() && (is_null($request->getParam('cons')) && is_null($request->getParam('user_cons')))) {
            $review->setCons(null);
        }

        if (is_array($image) && !empty($image) && !array_filter($image)) {
            $image = '';
        } elseif(is_array($image) && !empty($image)) {
            $image = implode(',', $image);
        }

        /**
         * save details
         */
        $detail = array(
            'title'         => $review->getTitle(),
            'video'         => $review->getVideo(),
            'image'         => $image,
            'detail'        => $review->getDetail(),
            'good_detail'   => $review->getGoodDetail(),
            'no_good_detail'=> $review->getNoGoodDetail(),
            'pros'          => (is_array($review->getPros())) ? implode(',', $review->getPros()) : $review->getPros(),
            'cons'          => (is_array($review->getCons())) ? implode(',', $review->getCons()) : $review->getCons(),
            'recommend_to'  => $review->getRecommendTo(),
            'nickname'      => $review->getNickname(),
            'response'      => ($review->getResponse() === null) ? '' : $review->getResponse(),
            'sizing'        => $review->getSizing(),
            'body_type'     => (int) $review->getBodyType(),
            'location'      => $review->getLocation(),
            'age'           => ((int) $review->getAge()) ? ((int) $review->getAge()) : null,
            'height'        => ((float) $review->getHeight()) ? (float) $review->getHeight() : null,
            'customer_email'=> $review->getCustomerEmail() ? $review->getCustomerEmail() : null
        );
        $detail = new Varien_Object($detail);
        Mage::dispatchEvent('detailedreview_mysql4_review_after_save', array(
            'review' => $review,
            'detail' => $detail
        ));
        $detail = $detail->getData();
        $select = $this->_getWriteAdapter()
            ->select()
            ->from($this->_reviewDetailTable, 'detail_id')
            ->where('review_id = ?', $review->getId());

        if ($detailId = (int) $this->_getWriteAdapter()->fetchOne($select)) {
            $this->_getWriteAdapter()->update(
                $this->_reviewDetailTable,
                $detail,
                "detail_id = $detailId"
            );
        } else {
            $detail['store_id']    = $review->getStoreId();
            $detail['customer_id'] = $review->getCustomerId();
            $detail['review_id']   = $review->getId();
            $detail['remote_addr'] = Mage::helper('core/http')->getRemoteAddr();
            $this->_getWriteAdapter()->insert($this->_reviewDetailTable, $detail);
        }

        // Save stores
        $stores = $review->getStores();
        if (!empty($stores)) {
            $condition = $this->_getWriteAdapter()->quoteInto('review_id = ?', $review->getId());
            $this->_getWriteAdapter()->delete($this->_reviewStoreTable, $condition);

            $insertedStoreIds = array();
            foreach ($stores as $storeId) {
                if (in_array($storeId, $insertedStoreIds)) {
                    continue;
                }

                $insertedStoreIds[] = $storeId;
                $storeInsert = array(
                    'store_id' => $storeId,
                    'review_id'=> $review->getId()
                );
                $this->_getWriteAdapter()->insert($this->_reviewStoreTable, $storeInsert);
            }
        }

        // re-aggregate ratings, that depend on this review
        $this->_aggregateRatings(
            $this->_loadVotedRatingIds($review->getId()),
            $review->getEntityPkValue()
        );
        
        $oldStatus = (int)$review->getOrigData('status_id');
        $newStatus = (int)$review->getData('status_id');
    
        // Reindex if it was a new review and it has been approved
        if ($oldStatus === 0 && (int)$review->getStatusId() === Mage_Review_Model_Review::STATUS_APPROVED ) {
            Mage::helper('detailedreview')->reindexProductAttr($review->getEntityPkValue());
        }
        // Reindex if a status was changed in existing review, ignore if new
        if ($oldStatus !== 0 && $oldStatus !== $newStatus) {
            Mage::helper('detailedreview')->reindexProductAttr($review->getEntityPkValue());
        }

        return $this;
    }

    /**
     * Check if engine of table is MyISAM
     *
     * @param $tableName
     * @return bool
     */
    public function isMyIsamEngine($tableName)
    {
        $adapter = $this->_getWriteAdapter();

        $select = 'SELECT engine'
            . ' FROM ' . $adapter->quoteIdentifier('INFORMATION_SCHEMA') . '.tables'
            . " WHERE table_name = '$tableName' AND"
            . " table_schema = '" . (string)Mage::getConfig()->getNode('global/resources/default_setup/connection/dbname') . "'";

        $tableEngineName = $adapter->fetchOne($select);

        return $tableEngineName == self::MYISAM_ENGINE_NAME;
    }

    /**
     * Remove table rows based on data from foreign table
     *
     * @param string $tableName
     * @param string $foreignColumnName
     * @param string $foreignTableName
     * @param string $foreignTableIdColumnName
     * @throws Exception
     *
     * @return bool|int
     */
    public function removeTableData($tableName, $foreignColumnName, $foreignTableName, $foreignTableIdColumnName)
    {
        $result = false;
        
        if (!$tableName) {
            return $result;
        }

        $ids = $this->getIdsForRemoving($tableName, $foreignColumnName, $foreignTableName, $foreignTableIdColumnName);
        
        if (!empty($ids)) {
            $adapter = $this->_getWriteAdapter();
            $adapter->beginTransaction();

            try {
                $result = $adapter->delete(
                    $tableName,
                    "{$foreignColumnName} IN (" . implode(', ', $ids) . ')'
                );
                $adapter->commit();
            } catch (Exception $e) {
                $adapter->rollBack();
            }
        }
        
        return $result;
    }

    /**
     * Get IDs for removing data from table
     *
     * @param string $tableName
     * @param string $foreignColumnName
     * @param string $foreignTableName
     * @param string $foreignTableIdColumnName
     * 
     * @return array|bool
     */
    protected function getIdsForRemoving($tableName, $foreignColumnName, $foreignTableName, $foreignTableIdColumnName)
    {
        $adapter = $this->_getWriteAdapter();

        $subQuery = new Zend_Db_Expr("
            SELECT 
                sub_table.id 
            FROM 
                {$tableName} sub_table 
                    INNER JOIN 
                {$foreignTableName} foreign_table ON sub_table.{$foreignColumnName} = foreign_table.{$foreignTableIdColumnName}
        ");

        $select = $adapter->select()
            ->distinct(true)
            ->from(array('main_table' => $tableName))
            ->where('main_table.id NOT IN (?)', $subQuery)
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array("{$foreignColumnName}" => "main_table.{$foreignColumnName}"));

        return $adapter->fetchCol($select);
    }

    /**
     * Get average sizing of product reviews
     * 
     * @return int
     */
    public function getAverageSizing()
    {
        $adapter = $this->_getWriteAdapter();

        /** @var Mage_Core_Model_Resource $coreResourceModel */
        $coreResourceModel = Mage::getSingleton('core/resource');

        $product = Mage::registry('product');

        $select = $adapter->select()
            ->from(
                array('main_table' => $coreResourceModel->getTableName('review/review')),
                array()
            )
            ->join(
                array('detail' => $coreResourceModel->getTableName('review/review_detail')),
                'main_table.review_id = detail.review_id',
                array()
            )
            ->join(
                array('store' => $coreResourceModel->getTableName('review/review_store')),
                'main_table.review_id = store.review_id',
                array()
            )
            ->join(
                array('entity' => $coreResourceModel->getTableName('review/review_entity')),
                'main_table.entity_id = entity.entity_id',
                array()
            )
            ->columns(new Zend_Db_Expr('ROUND(AVG(detail.sizing)) AS avg_sizing'))
            ->where("store.store_id = ?", Mage::app()->getStore()->getId())
            ->where("main_table.status_id = ?", Mage_Review_Model_Review::STATUS_APPROVED)
            ->where("entity.entity_code = ?", Mage_Review_Model_Review::ENTITY_PRODUCT_CODE)
            ->where("main_table.entity_pk_value = ?", $product->getId());
        
        return (int) $adapter->fetchOne($select);
    }
}
