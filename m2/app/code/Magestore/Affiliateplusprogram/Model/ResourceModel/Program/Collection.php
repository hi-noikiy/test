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
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplusprogram\Model\ResourceModel\Program;

/**
 * Class Collection
 * @package Magestore\Affiliateplusprogram\Model\ResourceModel\Program
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'program_id';
    protected $_storeViewId = null;
    /**
     * @var
     */
    protected $_addedTable;

    /**
     * @var \Magestore\Affiliateplusprogram\Model\Program
     */
    protected $_programModel;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magestore\Affiliateplusprogram\Model\Program $programModel
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
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_programModel = $programModel;
        $this->_objectManager = $objectManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Magestore\Affiliateplusprogram\Model\Program', 'Magestore\Affiliateplusprogram\Model\ResourceModel\Program');
    }
    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->_storeViewId;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setStoreId($value)
    {
        $this->_storeViewId = $value;
        return $this;
    }



    /**
     * @param array|string $field
     * @param null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition=null) {
        if ($storeId = $this->getStoreId()) {
            $attributes = array_merge(
                $this->_programModel->getStoreAttributes(),
                $this->_programModel->getTotalAttributes()
            );
            if (in_array($field, $attributes)) {
                if (!in_array($field, $this->_addedTable)) {
                    $this->getSelect()
                        ->joinLeft(
                            [
                                $field => $this->getTable(\Magestore\Affiliateplusprogram\Setup\InstallSchema::SCHEMA_PROGRAM_ACCOUNT)
                            ],
                            "main_table.program_id = $field.program_id" .
                            " AND $field.store_id = $storeId" .
                            " AND $field.attribute_code = '$field'",
                            array()
                        );
                    $this->_addedTable[] = $field;
                }
                return parent::addFieldToFilter("IFNULL($field.value_id, main_table.$field)", $condition);
            }
        }
        return parent::addFieldToFilter($field, $condition);
    }
}
