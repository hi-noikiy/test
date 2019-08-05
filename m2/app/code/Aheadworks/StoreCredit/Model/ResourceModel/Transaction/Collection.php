<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model\ResourceModel\Transaction;

use Aheadworks\StoreCredit\Api\Data\TransactionSearchResultsInterface;
use Aheadworks\StoreCredit\Model\Transaction;
use Aheadworks\StoreCredit\Model\ResourceModel\Transaction as TransactionResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Aheadworks\StoreCredit\Model\ResourceModel\Transaction\Collection
 */
class Collection extends AbstractCollection implements TransactionSearchResultsInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaInterface
     */
    private $searchCriteria;

    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(Transaction::class, TransactionResource::class);
    }

    /**
     * Add customer filter
     *
     * @param int|string $customerId
     * @return $this
     */
    public function addCustomerFilter($customerId)
    {
        $this->addFieldToFilter('customer_id', ['eq' => $customerId]);
        return $this;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return $this->searchCriteria;
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
        $this->searchCriteria = $searchCriteria;
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
     */
    public function setItems(array $items = null)
    {
        if (!$items) {
            return $this;
        }
        foreach ($items as $item) {
            $this->addItem($item);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'created_by') {
            return $this->addCreatedByFilter($condition);
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add created by filter
     *
     * @param string $createdBy
     * @return $this
     */
    public function addCreatedByFilter($createdBy)
    {
        $this->addFilter('created_by', $createdBy, 'public');
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->attachRelationTable(
            'admin_user',
            'created_by',
            'user_id',
            '',
            'created_by'
        );
        $this->attachRelationTable(
            'aw_sc_transaction_entity',
            'transaction_id',
            'transaction_id',
            '',
            'entities'
        );
        return parent::_afterLoad();
    }

    /**
     * {@inheritdoc}
     */
    protected function _renderFiltersBefore()
    {
        $this->joinLinkageTable('admin_user', 'created_by', 'user_id', 'created_by');
        parent::_renderFiltersBefore();
    }

    /**
     * Join to linkage table if filter is applied
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $linkageColumnName
     * @param string $columnFilter
     * @return void
     */
    private function joinLinkageTable(
        $tableName,
        $columnName,
        $linkageColumnName,
        $columnFilter
    ) {
        if ($this->getFilter($columnFilter)) {
            $linkageTableName = $columnFilter . '_table';
            $select = $this->getSelect();
            $select->joinLeft(
                [$linkageTableName => $this->getTable($tableName)],
                'main_table.' . $columnName . ' = ' . $linkageTableName . '.' . $linkageColumnName,
                []
            );
            switch ($columnFilter) {
                case 'created_by':
                    $this->addFilterToMap(
                        $columnFilter,
                        'CONCAT_WS(" ", ' . $linkageTableName . '.firstname, ' . $linkageTableName . '.lastname)'
                    );
                    break;
                default:
                    $this->addFilterToMap($columnFilter, $columnFilter . '_table.' . $columnFilter);
                    break;
            }
        }
    }

    /**
     * Attach entity table data to collection items
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $linkageColumnName
     * @param string $columnNameRelationTable
     * @param string $fieldName
     * @return void
     */
    private function attachRelationTable(
        $tableName,
        $columnName,
        $linkageColumnName,
        $columnNameRelationTable,
        $fieldName
    ) {
        $ids = $this->getColumnValues($columnName);
        if (count($ids)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from([$tableName . '_table' => $this->getTable($tableName)])
                ->where($tableName . '_table.' . $linkageColumnName . ' IN (?)', $ids);

            /** @var \Magento\Framework\DataObject $item */
            foreach ($this as $item) {
                $result = [];
                $id = $item->getData($columnName);
                foreach ($connection->fetchAll($select) as $data) {
                    if ($data[$linkageColumnName] == $id) {
                        switch ($fieldName) {
                            case 'created_by':
                                $result = $data['firstname'] . ' ' . $data['lastname'];
                                break;
                            case 'entities':
                                $result[$data['entity_type']] = [
                                    'entity_id'    => $data['entity_id'],
                                    'entity_label' => $data['entity_label']
                                ];
                                break;
                            default:
                                $result[] = $data[$columnNameRelationTable];
                                break;
                        }
                    }
                }
                $item->setData($fieldName, $result);
            }
        }
    }
}
