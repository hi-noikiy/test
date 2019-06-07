<?php

class Gearup_Blog_Model_RateAggregate extends Mage_Core_Model_Abstract {

    const BLOG_RATING_ENTITY = "blog";
    protected $_aggregateTable;
    protected $_ratingStoreTable;
    protected $_ratingVoteTable;
    protected $_resource;

    protected function _construct() {
        $this->_init('gearup_blog/RateAggregate');
        $this->_resource = Mage::getSingleton('core/resource');

        $this->_ratingStoreTable = $this->_resource->getTableName('rating/rating_store');
        $this->_aggregateTable = $this->_resource->getTableName('rating/rating_vote_aggregated');
        $this->_ratingVoteTable = $this->_resource->getTableName('rating/rating_option_vote');
    }

    
    
    public function aggregateEntityByRatingId($ratingId, $entityPkValue) {

        $readAdapter = $this->_resource->getConnection('core_read');
        $writeAdapter = $this->_resource->getConnection('core_write');

        try {
            $select = $readAdapter->select()
                    ->from($this->_aggregateTable, array('store_id', 'primary_id'))
                    ->where('rating_id = :rating_id')
                    ->where('entity_pk_value = :pk_value');
            $bind = array(':rating_id' => $ratingId, ':pk_value' => $entityPkValue);

            $oldData = $readAdapter->fetchPairs($select, $bind);



            $select = $readAdapter->select()
                    ->from(array('vote' => $this->_ratingVoteTable), array(
                        'vote_count' => new Zend_Db_Expr('COUNT(vote.vote_id)'),
                        'vote_value_sum' => new Zend_Db_Expr('SUM(vote.value)'),
                        'app_vote_count' => new Zend_Db_Expr("COUNT(vote.vote_id)"),
                        'app_vote_value_sum' => new Zend_Db_Expr("SUM(vote.value)")))
                    ->join(array('rstore' => $this->_ratingStoreTable), 'vote.rating_id=rstore.rating_id ', array('store_id'))
                    ->where('vote.rating_id = :rating_id')
                    ->where('vote.entity_pk_value = :pk_value')
                    ->group(array(
                'vote.rating_id',
                'vote.entity_pk_value',
                'rstore.store_id'
            ));

            $perStoreInfo = $readAdapter->fetchAll($select, $bind);

            $usedStores = array();
            foreach ($perStoreInfo as $row) {
                $saveData = array(
                    'rating_id' => $ratingId,
                    'entity_pk_value' => $entityPkValue,
                    'vote_count' => $row['vote_count'],
                    'vote_value_sum' => $row['vote_value_sum'],
                    'percent' => (($row['vote_value_sum'] / $row['vote_count']) / 5) * 100,
                    'percent_approved' => ($row['app_vote_count'] ? ((($row['app_vote_value_sum'] / $row['app_vote_count']) / 5) * 100) : 0),
                    'store_id' => $row['store_id'],
                );

                if (isset($oldData[$row['store_id']])) {
                    $condition = array('primary_id = ?' => $oldData[$row['store_id']]);
                    $writeAdapter->update($this->_aggregateTable, $saveData, $condition);
                } else {
                    $writeAdapter->insert($this->_aggregateTable, $saveData);
                }

                $usedStores[] = $row['store_id'];
            }

            $toDelete = array_diff(array_keys($oldData), $usedStores);

            foreach ($toDelete as $storeId) {
                $condition = array('primary_id = ?' => $oldData[$storeId]);
                $writeAdapter->delete($this->_aggregateTable, $condition);
            }
        } catch (Exception $e) {
            Mage::log($e->getMessage(), true, 'gearup_blog.log');
        }
    }

}
