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
class MageWorkshop_DetailedReview_Model_Mysql4_Review_Collection extends Mage_Review_Model_Mysql4_Review_Collection
{
    protected $_salesFlatOrderTable;
    protected $_salesFlatOrderItemTable;
    protected $_purchaseInfoTable;
    protected $_ratingOptionVoteTable;
    protected $_reviewHelpfulTable;

    protected $_appliedFilters = array();

    /**
     * @inherit
     */
    public function __construct()
    {
        parent::__construct();
        if (!Mage::getStoreConfig('detailedreview/settings/enable')) {
            return;
        }
        $resources = Mage::getSingleton('core/resource');
        $this->_salesFlatOrderTable = $resources->getTableName('sales/order');
        $this->_salesFlatOrderItemTable = $resources->getTableName('sales/order_item');
        $this->_ratingOptionVoteTable = $resources->getTableName('rating/rating_option_vote');
        $this->_reviewHelpfulTable = $resources->getTableName('detailedreview/review_helpful');
        $this->_purchaseInfoTable = $resources->getTableName('detailedreview/purchase');
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        if (!Mage::getStoreConfig('detailedreview/settings/enable')) {
            return $this;
        }

        $this->getSelect()
            ->columns(array(
                'detail.video',
                'detail.image',
                'detail.response',
                'detail.no_good_detail',
                'detail.good_detail',
                'detail.sizing',
                'detail.body_type',
                'detail.location',
                'detail.age',
                'detail.height',
                'detail.pros',
                'detail.cons',
                'detail.recommend_to',
                'detail.customer_email'
            )
        );
        Mage::dispatchEvent('detailedreview_mysql4_review_collection_init', array(
            'collection' => $this
        ));
        return $this;
    }


    /**
     * This filter used in
     *
     * @param array|string $field
     * @param null $condition
     * @return $this|Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function addFieldToFilter($field, $condition = null)
    {
        switch($field) {
            case 'type':
                if ($condition == 1) {
                    $conditionParts = array(
                        $this->_getConditionSql('detail.customer_id', array('is' => new Zend_Db_Expr('NULL'))),
                        $this->_getConditionSql('rdetail.store_id', array('eq' => Mage_Core_Model_App::ADMIN_STORE_ID))
                    );
                    $conditionSql = implode(' AND ', $conditionParts);
                } elseif ($condition == 2) {
                    $conditionSql = $this->_getConditionSql('detail.customer_id', array('gt' => 0));
                } else {
                    $conditionParts = array(
                        $this->_getConditionSql('detail.customer_id', array('is' => new Zend_Db_Expr('NULL'))),
                        $this->_getConditionSql('rdetail.store_id', array('neq' => Mage_Core_Model_App::ADMIN_STORE_ID))
                    );
                    $conditionSql = implode(' AND ', $conditionParts);
                }
                $this->getSelect()->where($conditionSql);
                break;

            default:
                parent::addFieldToFilter($field, $condition);
                break;
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function resetTotalRecords()
    {
        $this->_totalRecords = null;
        return $this;
    }

    /**
     * @param string $sort
     * @return $this
     */
    public function setCustomOrder($sort = 'default')
    {
        switch ($sort) {
            case 'date_asc':
                $this->setDateOrder('ASC');
                break;
            case 'rate_desc':
                $this->setRateOrder('DESC');
                break;
            case 'rate_asc':
                $this->setRateOrder('ASC');
                break;
            case 'most_helpful':
                $this->setHelpfulOrder();
                break;
            case 'ownership':
                $this->setOwnershipOrder();
                break;
            default:
                $this->setDateOrder();
                break;
        }
        return $this;
    }

    /**
     * @param string $dir
     */
    protected function setRateOrder($dir)
    {
        $fromTables = $this->_select->getPart(Zend_Db_Select::FROM);
        if (!isset($fromTables['rov'])) {
            $this->getSelect()
                 ->joinLeft(
                    array('rov' => $this->_ratingOptionVoteTable),
                    'rov.review_id = main_table.review_id',
                    array('rate_value' => 'avg(rov.value)')
                 )
                 ->group('main_table.review_id');
        }
        $this->setOrder('rate_value', $dir);
    }

    /**
     * @return $this
     */
    public function addHelpfulInfo()
    {
        if (!isset($this->_appliedFilters['helpful'])) {
            $this->getSelect()
                ->joinLeft(
                    array('rh' => $this->_reviewHelpfulTable),
                    'rh.review_id = main_table.review_id AND rh.is_helpful = 1',
                    array('count_helpful' => 'COUNT(DISTINCT rh.id)')
                )
                ->joinLeft(
                    array('ruh' => $this->_reviewHelpfulTable),
                    'ruh.review_id = main_table.review_id AND ruh.is_helpful = 0',
                    array('count_unhelpful' => 'COUNT(DISTINCT ruh.id)')
                )
                ->group('main_table.review_id');

            $this->_appliedFilters['helpful'] = true;
        }
        return $this;
    }

    protected function setHelpfulOrder()
    {
        $this->addHelpfulInfo();
        $this->setOrder('count_helpful', Varien_Data_Collection::SORT_ORDER_DESC);
        $this->setOrder('count_unhelpful', Varien_Data_Collection::SORT_ORDER_ASC);
    }
    
    /**
     * @param int|null $parentId
     * @param array $productIds
     * @return $this
     */
    public function addOwnershipInfo($parentId = null, $productIds = array())
    {
        if (!isset($this->_appliedFilters['ownership'])) {
            if (empty($productIds)) {
                $this->getSelect()
                    ->joinLeft(
                        array('p' => $this->_purchaseInfoTable),
                        'p.customer_email = detail.customer_email'
                        . ' AND p.product_id = main_table.entity_pk_value'
                        . ' AND p.store_id = store.store_id',
                        array('bought_in' => 'p.created_at')
                    )
                    ->group('main_table.review_id');
            } else {
                $connection = $this->getConnection();
                $onCondition1 = 'p.customer_email = detail.customer_email'
                    . ' AND p.product_id = ?'
                    . ' AND p.store_id = store.store_id';
                
                $onCondition2 = 'p2.customer_email = detail.customer_email AND p2.product_id IN (?)';
                
                $this->getSelect()
                    ->joinLeft(
                        array('p' => $this->_purchaseInfoTable),
                        $connection->quoteInto($onCondition1, $parentId),
                        array()
                    )
                    ->joinLeft(
                        array('p2' => $this->_purchaseInfoTable),
                        $connection->quoteInto($onCondition2, $productIds),
                        array('bought_in' => 'p2.created_at')
                    )
                    ->group('main_table.review_id');
            }

            $this->_appliedFilters['ownership'] = true;
        }
        return $this;
    }

    protected function setOwnershipOrder()
    {
        $this->addOwnershipInfo();
        $this->setOrder('bought_in', 'DESC');
    }

    /**
     * @return $this
     */
    public function addVerifiedBuyersFilter()
    {
        $this->addOwnershipInfo();
        $this->addFieldToFilter('p.created_at', array('neq' => ''));
        return $this;
    }

    /**
     * @return $this
     */
    public function addVideoFilter()
    {
        $this->addFieldToFilter('detail.video', array('neq' => ''));
        return $this;
    }

    /**
     * @return $this
     */
    public function addImagesFilter()
    {
        $this->addFieldToFilter('detail.image', array('neq' => ''));
        return $this;
    }

    public function addManyResponseFilter()
    {
        $this->addFieldToFilter('detail.response', array('neq' => ''));
        return $this;
    }

    /**
     * @return $this
     */
    public function addHighestContributorFilter()
    {
        $reviewDetailTable = Mage::getSingleton('core/resource')->getTableName('review/review_detail');
        $select = clone $this->getSelect();
        $select->reset();
        $select->from($reviewDetailTable, array('customer_id'))
            ->group('customer_id')
            ->having('customer_id IS NOT NULL')
            ->having('COUNT('.$reviewDetailTable.'.review_id) > ?', (int) Mage::getStoreConfig('detailedreview/show_review_info_settings/qty_items_in_highest_contributors'));
        $this->getSelect()->where('detail.customer_id IN ?', $select);
        return $this;
    }

    /**
     * @param int $range
     * @return $this
     */
    public function addDateRangeFilter($range)
    {
        $quoteDate = 0;
        if ($range == 2) {
            $quoteDate = mktime(0, 0, 0, date('m'), date('d') - 7, date('Y'));
        } elseif ($range == 3) {
            $quoteDate = mktime(0, 0, 0, date('m'), date('d') - 7 * 4, date('Y'));
        } elseif ($range == 4) {
            $quoteDate = mktime(0, 0, 0, date('m') - 6, date('d'), date('Y'));
        }
        $quoteDate = Mage::getSingleton('core/date')->gmtDate(null, $quoteDate);
        $this->addFilter('date_range',
            $this->getConnection()->quoteInto('main_table.created_at > ?', $quoteDate),
            'string');
        return $this;
    }

    /**
     * @param $from
     * @param $to
     * @return $this
     */
    public function addDateFromToFilter($from, $to)
    {
        $quoteFromDate = Mage::getSingleton('core/date')->gmtDate(null, $from);
        $quoteFromTo = Mage::getSingleton('core/date')->gmtDate(null, $to);

        $this->addFilter('date_from',
            $this->getConnection()->quoteInto('main_table.created_at >= ?', $quoteFromDate),
            'string');
        $this->addFilter('date_to',
            $this->getConnection()->quoteInto('main_table.created_at <= ?', $quoteFromTo),
            'string');
        return $this;
    }

    /**
     * @param string $query
     * @return $this
     */
    public function addKeywordsFilter($query = '')
    {
        if ($query) {
            $fields = 'detail.title LIKE ? or detail.detail LIKE ? or detail.good_detail LIKE ? or detail.no_good_detail LIKE ?';
            $eventData = new Varien_Object();

            $eventData->setData('fields', $fields);
            Mage::dispatchEvent('detailedreview_mysql4_review_collection_keywordsfilter', array(
                'fields' => $eventData
            ));
            $fields = $eventData->getData('fields');
            $this->addFilter(
                'keywords',
                $this->getConnection()->quoteInto($fields, "%$query%"),
                'string'
            );
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function addUserReviewFilter()
    {
        $this->addFieldToFilter('detail.customer_id', array('eq' =>  Mage::getSingleton('customer/session')->getCustomer()->getId()));
        return $this;
    }

    /**
     * @return Varien_Db_Select
     */
    public function getSelectCountSql()
    {
        if (!Mage::getStoreConfig('detailedreview/settings/enable')) {
            return parent::getSelectCountSql();
        }

        $this->_renderFilters();

        $select = clone $this->getSelect();

        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);

        $havingArray = $select->getPart(Zend_Db_Select::HAVING);
        $havingKeys = array();
        if (count($havingArray)) {
            foreach ($havingArray as $having) {
                $havingKeys[] = preg_replace('/.*?([A-Za-z0-9_.-]+) .*/', '$1', $having);
            }

            $columns = $select->getPart(Zend_Db_Select::COLUMNS);
            $select->reset(Zend_Db_Select::COLUMNS);

            foreach ($columns as $column) {
                if (in_array($column[2], $havingKeys)) {
                    $select->columns(array($column[2] => $column[1]));
                    $havingKeys = array_diff($havingKeys, array($column[2]));
                }
            }
        } else {
            $select->reset(Zend_Db_Select::COLUMNS);
        }

        $select->columns('main_table.review_id');

        $countSelect = clone $this->getSelect();
        $countSelect->reset();
        $countSelect->from(array('virtual' => new Zend_Db_Expr("($select)")), 'count(1)');

        return $countSelect;
    }

    /**
     * @return $this
     */
    public function  __clone()
    {
        $this->_select = clone $this->_select;
        return $this;
    }

    /**
     * @return array
     */
    public function getIds()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');
        return $this->getConnection()->fetchCol($idsSelect);
    }
}
