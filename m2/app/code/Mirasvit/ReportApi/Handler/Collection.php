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
 * @package   mirasvit/module-report
 * @version   1.3.16
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportApi\Handler;

use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Mirasvit\ReportApi\Api\Config\AggregatorInterface;
use Mirasvit\ReportApi\Api\Config\CollectionInterface;
use Magento\Framework\App\ResourceConnection;
use Mirasvit\ReportApi\Api\Config\RelationInterface;
use Mirasvit\ReportApi\Api\Config\TypeInterface;
use Mirasvit\ReportApi\Api\Config\ColumnInterface;
use Mirasvit\ReportApi\Api\Config\TableInterface;
use Mirasvit\ReportApi\Api\RequestInterface;
use Mirasvit\ReportApi\Config\Entity\Table;
use Mirasvit\ReportApi\Config\Schema;
use Mirasvit\ReportApi\Service\SelectService;
use PDepend\Util\Type;

class Collection implements CollectionInterface
{
    /**
     * @var Select
     */
    private $select;

    /**
     * @var SelectService
     */
    private $selectService;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var int
     */
    private $pageSize;

    /**
     * @var int
     */
    private $currentPage;

    /**
     * @var array
     */
    private $items = [];

    /**
     * @var Schema
     */
    protected $schema;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        SelectFactory $selectFactory,
        SelectService $selectService,
        ResourceConnection $resource,
        ObjectManagerInterface $objectManager,
        Schema $schema
    ) {
        $this->resource = $resource;
        $this->selectService = $selectService;
        $this->selectFactory = $selectFactory;
        $this->select = $selectFactory->create();
        $this->objectManager = $objectManager;
        $this->schema = $schema;
    }

    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;

        $baseTable = $this->schema->getTable($request->getTable());

        $this->connection = $this->resource->getConnection($baseTable->getConnectionName());

        $this->select->setBaseTable($baseTable)
            ->limitPage($request->getCurrentPage(), $request->getPageSize());

        foreach ($request->getColumns() as $identifier) {
            $column = $this->schema->getColumn($identifier);
            $clone = clone $column;


            if ($this->selectService->getRelationType($baseTable, $column->getTable()) == RelationInterface::TYPE_ONE) {
                //                echo '^';
                $this->select->addColumnToSelect($column);
            } else {
                //                echo '*';
                /** @var Table $table */
                $table = $this->selectService->createTemporaryTable($clone, $request, $baseTable);

                $clone->setTable($table);

                if ($clone->getAggregator()->getType() == AggregatorInterface::TYPE_COUNT) {
                    $agg = $this->objectManager->create($this->schema->getAggregator(AggregatorInterface::TYPE_SUM));
                    $clone->setAggregator($agg);
                }

                if ($clone->getType()->getType() == TypeInterface::TYPE_PERCENT) {
                    $agg = $this->objectManager->create($this->schema->getAggregator(AggregatorInterface::TYPE_AVERAGE));
                    $clone->setAggregator($agg);
                }

                $clone->setExpression('%1');
                $clone->setFields([$clone->getName()]);

                $this->select->addColumnToSelect($clone, $column->getIdentifier());
            }
        }

        foreach ($request->getFilters() as $filter) {
            $column = $this->schema->getColumn($filter->getColumn());

            if ($this->selectService->getRelationType($baseTable, $column->getTable()) == RelationInterface::TYPE_ONE) {
                $this->select->addColumnToFilter($this->schema->getColumn($filter->getColumn()), [
                    $filter->getConditionType() => $filter->getValue(),
                ]);
            } else {
                /** @var Table $table */
                $table = $this->selectService->createTemporaryTable($column, $request, $baseTable);

                $this->select->joinRight($table->getName(),
                    $table->getPkField()->toDbExpr() . '=' . $baseTable->getPkField()->toDbExpr(),
                    []
                );
            }
        }

        foreach ($request->getSortOrders() as $sortOrder) {
            $column = $this->schema->getColumn($sortOrder->getColumn());

            if ($this->selectService->getRelationType($baseTable, $column->getTable()) == RelationInterface::TYPE_ONE) {
                $this->select->addColumnToOrder($column, $sortOrder->getDirection());
            } else {
                /** @var Table $table */
                $table = $this->selectService->createTemporaryTable($column, $request, $baseTable);

                $clone = clone $column;
                $clone->setTable($table);

                if ($clone->getAggregator()->getType() == AggregatorInterface::TYPE_COUNT) {
                    $agg = $this->objectManager->create($this->schema->getAggregator(AggregatorInterface::TYPE_SUM));
                    $clone->setAggregator($agg);
                }
                $clone->setExpression('%1');
                $clone->setFields([$clone->getName()]);

                $this->select->joinLeft($table->getName(),
                    $table->getPkField()->toDbExpr() . '=' . $baseTable->getPkField()->toDbExpr(),
                    []
                )->order($clone->toDbExpr() . ' ' . $sortOrder->getDirection());
            }
        }

        $this->select->addColumnToGroup($this->schema->getColumn($request->getDimension()));


        return $this;
    }


    public function count()
    {
        $this->loadData();

        return count($this->items);
    }

    public function getIterator()
    {
        $this->loadData();

        return new \ArrayIterator($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function loadData()
    {
        $this->selectService->applyTimeZone($this->connection);

        $rows = $this->connection->fetchAll($this->select);

        foreach ($rows as $row) {
            $this->items[] = $row;
        }

        $this->selectService->restoreTimeZone($this->connection);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->select->__toString();
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $this->select = clone $this->select;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        $countSelect = clone $this->select;
        $countSelect->reset(\Zend_Db_Select::ORDER)
            ->reset(\Zend_Db_Select::LIMIT_COUNT)
            ->reset(\Zend_Db_Select::LIMIT_OFFSET)
            ->reset(\Zend_Db_Select::COLUMNS);

        $countSelect->columns();

        $select = 'SELECT COUNT(*) FROM (' . $countSelect->__toString() . ') as cnt';

        $result = $this->connection->fetchOne($select);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotals()
    {
        $select = clone $this->select;
        $select->reset(\Zend_Db_Select::ORDER)
            ->reset(\Zend_Db_Select::LIMIT_COUNT)
            ->reset(\Zend_Db_Select::LIMIT_OFFSET)
            ->reset(\Zend_Db_Select::GROUP);

        $result = [];

        $this->selectService->applyTimeZone($this->connection);
        $rows = $this->connection->fetchAll($select);
        $this->selectService->restoreTimeZone($this->connection);

        foreach ($rows as $row) {
            foreach ($row as $k => $v) {
                if (!isset($result[$k])) {
                    $result[$k] = null;
                }
                if (is_numeric($v)) {
                    $result[$k] += (float)$v;
                } else {
                    $result[$k] .= ','.$v;
                }
            }
        }

        $columnNames = array_keys($result);
        foreach ($columnNames as $columnName) {
            if ($columnName == 'pk') {
                continue;
            }
            $column = $this->schema->getColumn($columnName);

            if ($this->selectService->getRelationType(
                    $column->getTable(),
                    $this->schema->getTable($this->request->getTable())
                ) == RelationInterface::TYPE_MANY) {
                $result[$columnName] = null;
                continue;
            }

            if ($column->getType()->getValueType() === TypeInterface::VALUE_TYPE_STRING) {
                $values = [];
                foreach (explode(',', $result[$columnName]) as $value) {
                    if ($value && !in_array($value, $values, true)) {
                        $values[] = $value;
                    }
                }

                $result[$columnName] = implode(', ', $values);
            } elseif(!in_array($column->getType()->getValueType(), [TypeInterface::VALUE_TYPE_NUMBER])) {
                $result[$columnName] = null;
            } elseif ($column->getAggregator()->getType() == AggregatorInterface::TYPE_AVERAGE) {
                $result[$columnName] /= count($rows);
            } elseif ($column->getType()->getType() == TypeInterface::TYPE_PERCENT) {
                $result[$columnName] /= count($rows);
            } elseif ($column->getType()->getType() == TypeInterface::TYPE_PK && $column->getAggregator()->getType() == AggregatorInterface::TYPE_NONE) {
                $result[$columnName] = null;
            }
        }

        return $result;
    }
}