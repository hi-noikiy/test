<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplusprogram
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

namespace Magestore\Affiliateplusprogram\Model\ResourceModel\Program\Grid;

use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Magestore\Affiliateplusprogram\Model\ResourceModel\Program\Collection as ProgramCollection;

/**
 * Class Collection
 * @package Magestore\Affiliateplusprogram\Model\ResourceModel\Program\Grid
 */
class Collection extends ProgramCollection  implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * @var \Magestore\Affiliateplusprogram\Model\ResourceModel\Value
     */
    protected $_programValueCollection;

    /**
     * @var array
     */
    protected $_storeField = [
        'name',
        'affiliate_type',
        'status',
        'description',
        'commission_type',
        'commission',
        'sec_commission',
        'sec_commission_type',
        'secondary_commission',
        'discount_type',
        'discount',
        'sec_discount',
        'sec_discount_type',
        'secondary_discount',
        'customer_group_ids',
        'show_in_welcome',
        'use_tier_config',
        'max_level',
        'tier_commission',
        'use_sec_tier',
        'sec_tier_commission',
    ];
    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magestore\Affiliateplusprogram\Model\Program $programModel
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $mainTable
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resourceModel
     * @param string $model
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magestore\Affiliateplusprogram\Model\Program $programModel,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\Affiliateplusprogram\Model\ResourceModel\Value\CollectionFactory $programValueCollection,
        $mainTable,
        $resourceModel,
        $model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_programValueCollection = $programValueCollection;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $programModel,
            $objectManager,
            $connection,
            $resource
        );
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }



    /**
     * @return $this
     */


    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        if ($this->_storeId) {
            $storeField = $this->_storeField;
            foreach ($storeField as $value) {
                $brandValue = $this->_programValueCollection->create()
                    ->addFieldToFilter('store_id', $storeId)
                    ->addFieldToFilter('attribute_code', $value)
                    ->getSelect()
                    ->assemble();

                $this->getSelect()
                    ->joinLeft(
                        [
                            'program_value_' . $value => new \Zend_Db_Expr("($brandValue)"),
                        ],
                        'main_table.program_id = program_value_' . $value . '.program_id',
                        [$value => 'IF(program_value_' . $value . '.value IS NULL,main_table.' . $value . ',program_value_' . $value . '.value)']);
            }
        }

        return $this;
    }

    /**
     * Retrieve all ids for collection
     * Backward compatibility with EAV collection
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }
}
