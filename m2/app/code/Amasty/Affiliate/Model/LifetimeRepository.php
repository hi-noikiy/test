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
use Amasty\Affiliate\Model\ResourceModel\Lifetime\Collection;
use Magento\Framework\Api\SortOrder;

class LifetimeRepository extends AbstractRepository implements \Amasty\Affiliate\Api\LifetimeRepositoryInterface
{

    /**
     * @var ResourceModel\Lifetime
     */
    private $resource;

    /**
     * @var LifetimeFactory
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
     * @var ResourceModel\Lifetime\CollectionFactory
     */
    private $collectionFactory;

    /**
     * LifetimeRepository constructor.
     * @param \Amasty\Affiliate\Model\ResourceModel\Lifetime $resource
     * @param \Amasty\Affiliate\Model\LifetimeFactory $factory
     * @param \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Amasty\Affiliate\Model\ResourceModel\Lifetime\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Amasty\Affiliate\Model\ResourceModel\Lifetime $resource,
        \Amasty\Affiliate\Model\LifetimeFactory $factory,
        \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory $searchResultsFactory,
        \Amasty\Affiliate\Model\ResourceModel\Lifetime\CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\LifetimeInterface $entity)
    {
        if ($entity->getLifetimeId()) {
            $entity = $this->get($entity->getLifetimeId())->addData($entity->getData());
        }

        try {
            $this->resource->save($entity);
            unset($this->entities[$entity->getLifetimeId()]);
        } catch (\Exception $e) {
            if ($entity->getLifetimeId()) {
                throw new CouldNotSaveException(
                    __('Unable to save lifetime with ID %1. Error: %2', [$entity->getLifetimeId(), $e->getMessage()])
                );
            }
            throw new CouldNotSaveException(__('Unable to save new lifetime. Error: %1', $e->getMessage()));
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!isset($this->entities[$id])) {
            /** @var \Amasty\Affiliate\Model\Lifetime $entity */
            $entity = $this->resource->load($this->factory->create(), $id);
            if (!$entity->getLifetimeId()) {
                throw new NoSuchEntityException(__('Lifetime with specified ID "%1" not found.', $id));
            }
            $this->entities[$id] = $entity;
        }
        return $this->entities[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Data\LifetimeInterface $entity)
    {
        try {
            $this->resource->delete($entity);
            unset($this->entities[$entity->getId()]);
        } catch (\Exception $e) {
            if ($entity->getLifetimeId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove lifetime with ID %1. Error: %2', [$entity->getLifetimeId(), $e->getMessage()])
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove lifetime. Error: %1', $e->getMessage()));
        }
        return true;
    }
}
