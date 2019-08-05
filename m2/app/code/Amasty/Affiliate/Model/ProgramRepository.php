<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Model;

use Amasty\Affiliate\Api\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Amasty\Affiliate\Model\ResourceModel\Program\Collection;
use Magento\Framework\Api\SortOrder;

class ProgramRepository extends AbstractRepository implements \Amasty\Affiliate\Api\ProgramRepositoryInterface
{

    /**
     * @var ResourceModel\Program
     */
    private $resource;

    /**
     * @var ProgramFactory
     */
    private $factory;

    /**
     * @var array
     */
    private $entities = [];

    /**
     * @var \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var ResourceModel\Program\CollectionFactory
     */
    private $collectionFactory;

    /**
     * ProgramRepository constructor.
     * @param \Amasty\Affiliate\Model\ResourceModel\Program $resource
     * @param \Amasty\Affiliate\Model\ProgramFactory $factory
     * @param \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Amasty\Affiliate\Model\ResourceModel\Program $resource,
        \Amasty\Affiliate\Model\ProgramFactory $factory,
        \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory $searchResultsFactory,
        \Amasty\Affiliate\Model\ResourceModel\Program\CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\ProgramInterface $entity)
    {
        try {
            $this->resource->save($entity);
            unset($this->entities[$entity->getProgramId()]);
        } catch (\Exception $e) {
            if ($entity->getProgramId()) {
                throw new CouldNotSaveException(
                    __('Unable to save program with ID %1. Error: %2', [$entity->getProgramId(), $e->getMessage()])
                );
            }
            throw new CouldNotSaveException(__('Unable to save new program. Error: %1', $e->getMessage()));
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!isset($this->entities[$id])) {
            /** @var \Amasty\Affiliate\Model\Program $entity */
            $entity = $this->resource->load($this->factory->create(), $id);
            if (!$entity->getProgramId()) {
                throw new NoSuchEntityException(__('Program with specified ID "%1" not found.', $id));
            }
            $this->entities[$id] = $entity;
        }
        return $this->entities[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Data\ProgramInterface $entity)
    {
        try {
            $this->resource->delete($entity);
            unset($this->entities[$entity->getId()]);
        } catch (\Exception $e) {
            if ($entity->getProgramId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove program with ID %1. Error: %2', [$entity->getProgramId(), $e->getMessage()])
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove program. Error: %1', $e->getMessage()));
        }
        return true;
    }
}
